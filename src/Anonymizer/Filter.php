<?php

declare(strict_types=1);

namespace Anonymizer;

final class Filter
{
    /**
        * Anonymize specified column data.
        *
        * @author          Martin Latter
        * @copyright       Martin Latter 04/07/2021
        * @version         0.09
        * @license         GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link            https://github.com/Tinram/Database-Anonymizer.git
        * @package         Anonymizer
    */

    use Logger;

    /** @var object $db */
    private $db;

    /** @var integer $iNumRows, number of rows */
    private $numRows = 0;

    /**
        * @param   object $oDB
        * @param   array<mixed> $aFilter
        * @param   integer $iNumRows, number of rows to process
    */
    public function __construct(Connect $oDB, array $aFilters, int $iNumRows = 0)
    {
        $this->db = $oDB;
        $this->numRows = $iNumRows;
        $this->filterTables($aFilters);
    }

    /**
        * Anonymize specific columns with junk data.
        *
        * @param   array<mixed> $aTables, table:fields
        * @return  void
    */
    private function filterTables(array $aTables): void
    {
        $rxPattern = '/([0-9]+)/';

        foreach ($aTables as $sTable => $aFields)
        {
            # get PK of table
            $sPrimaryKey = '
                SELECT
                    COLUMN_NAME
                FROM
                    information_schema.COLUMNS
                WHERE
                    COLUMN_KEY = "PRI"
                AND
                    TABLE_SCHEMA = "' . $this->db->dbname . '"
                AND
                    TABLE_NAME = "' . $sTable . '"';

            $rResult = $this->db->conn->query($sPrimaryKey);
            $sPrimaryKey = $rResult->fetch_row()[0];

            ###################################################

            $aSQLFields = [];

            foreach ($aFields as $f)
            {
                $aSQLFields[] = '"' . $f . '"';
            }

            $sColumns = join(',', $aSQLFields);

            $sColAtts = '
                SELECT
                    COLUMN_NAME, DATA_TYPE, COLUMN_TYPE
                FROM
                    information_schema.COLUMNS
                WHERE
                    TABLE_SCHEMA = "' . $this->db->dbname . '"
                AND
                    TABLE_NAME = "' . $sTable . '"
                AND
                    COLUMN_NAME IN(' . $sColumns . ')';

            $rResult = $this->db->conn->query($sColAtts);

            while ($aRow = $rResult->fetch_assoc())
            {
                $rx = preg_match($rxPattern, $aRow['COLUMN_TYPE'], $aM);
                $iMaxLen = 0;

                if (count($aM) === 0) # for MySQL v.8 schema-deprecated/information_schema-removed integer display widths
                {
                    switch ($aRow['DATA_TYPE'])
                    {
                        case 'int':
                            $iMaxLen = 9;
                        break;

                        case 'tinyint':
                            $iMaxLen = 2;
                        break;

                        case 'smallint':
                            $iMaxLen = 4;
                        break;

                        case 'mediumint':
                            $iMaxLen = 6;
                        break;

                        default:
                            $iMaxLen = 0;
                    }
                }
                else
                {
                    switch ($aRow['COLUMN_TYPE'])
                    {
                        case 'text':
                            $iMaxLen = 255;
                        break;

                        case 'date':
                        case 'datetime':
                        case 'timestamp':
                        case 'float':
                        case 'decimal':
                            $iMaxLen = 0;
                        break;

                        default:
                            $iMaxLen = (int) ($aM[0] - 1);
                    }
                }

                $aColAttributes[$aRow['COLUMN_NAME']] = ['data_type' => $aRow['DATA_TYPE'], 'max_length' => $iMaxLen];
            }

            ###################################################

            $lt = $this->db->conn->query('LOCK TABLES ' . $sTable . ' WRITE');

            $this->db->conn->begin_transaction(); # transactions ~4x faster per 5000 rows

            for ($i = 1; $i <= $this->numRows; $i++)
            {
                $aDBFields = [];

                foreach ($aFields as $sFieldName)
                {
                    $sTypes = 'si'; # default parameter types

                    # act on column name hints first
                    if (strpos($sFieldName, 'name') !== false)
                    {
                        $aDBFields[$sFieldName] = CharGenerator::generateName(15, 'gibberish');
                    }
                    else if (strpos($sFieldName, 'email') !== false)
                    {
                        $aDBFields[$sFieldName] = CharGenerator::generateEmail(10, 'gibberish');
                    }
                    else if (strpos($sFieldName, 'phone') !== false)
                    {
                        $aDBFields[$sFieldName] = CharGenerator::generateNumber(14);
                    }
                    else if (strpos($sFieldName, 'date') !== false)
                    {
                        $aDBFields[$sFieldName] = CharGenerator::generateDate(5, 'date', 'day');
                    }
                    else if (strpos($sFieldName, 'timestamp') !== false)
                    {
                        $aDBFields[$sFieldName] = CharGenerator::generateDate(11, 'date');
                    }
                    else
                    {
                        # guesstimates from data types
                        if ($aColAttributes[$sFieldName]['data_type'] === 'char' || $aColAttributes[$sFieldName]['data_type'] === 'varchar')
                        {
                            $aDBFields[$sFieldName] = CharGenerator::generateText($aColAttributes[$sFieldName]['max_length'], 'alpha');
                        }
                        else if (strpos($aColAttributes[$sFieldName]['data_type'], 'int') !== false)
                        {
                            $aDBFields[$sFieldName] = CharGenerator::generateNumber($aColAttributes[$sFieldName]['max_length']);
                            $sTypes = 'ii';
                        }
                        else if ($aColAttributes[$sFieldName]['data_type'] === 'decimal' || $aColAttributes[$sFieldName]['data_type'] === 'float' || $aColAttributes[$sFieldName]['data_type'] === 'double')
                        {
                            $aDBFields[$sFieldName] = CharGenerator::generateNumber(2);
                            # for $sTypes here, rely on implict casting
                        }
                        else if ($aColAttributes[$sFieldName]['data_type'] === 'date')
                        {
                            $aDBFields[$sFieldName] = CharGenerator::generateDate(5, 'date', 'day');
                        }
                        else if ($aColAttributes[$sFieldName]['data_type'] === 'datetime' || $aColAttributes[$sFieldName]['data_type'] === 'timestamp')
                        {
                            $aDBFields[$sFieldName] = CharGenerator::generateDate(11, 'date');
                        }
                        else
                        {
                            $aDBFields[$sFieldName] = CharGenerator::generateText($aColAttributes[$sFieldName]['max_length'], 'alpha');
                        }
                    }

                    $sUpdate = '
                        UPDATE ' . $sTable . '
                        SET ' . $sFieldName . ' = ?
                        WHERE ' . $sPrimaryKey . ' = ?';

                    $oStmt = $this->db->conn->stmt_init();
                    $oStmt->prepare($sUpdate);
                    $oStmt->bind_param($sTypes, $aDBFields[$sFieldName], $i);
                    $oStmt->execute();
                    $oStmt->close();
                }
            }

            $this->db->conn->commit();

            $ut = $this->db->conn->query('UNLOCK TABLES');

            $sMessage = 'Filtered table ' . $sTable . '.';
            Logger::log($sMessage, true);
        }
    }
}
