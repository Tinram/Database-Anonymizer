<?php

declare(strict_types=1);

namespace Anonymizer;

final class Clip
{
    /**
        * Clip tables.
        *
        * @author          Martin Latter
        * @copyright       Martin Latter 04/07/2021
        * @version         0.06
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
        * @param   integer $iNumRows, number of rows to clip to
    */
    public function __construct(Connect $oDB, int $iNumRows = 0)
    {
        $this->db = $oDB;
        $this->numRows = $iNumRows;
        $this->clipTables();
    }

    /**
        * Clip the table rows to the specified number of rows avoiding expensive DELETEs.
        *
        * @return  void
    */
    private function clipTables(): void
    {
        $aTableNames = [];

        $sTableNamesQuery = '
            SELECT TABLE_NAME
            FROM information_schema.tables
            WHERE
                TABLE_TYPE = "BASE TABLE"
            AND TABLE_SCHEMA = "' . $this->db->dbname . '"';

        $rResult = $this->db->conn->query($sTableNamesQuery);

        while ($aRow = $rResult->fetch_assoc())
        {
            $aTableNames[] = $aRow['TABLE_NAME'];
        }

        $sTempTable = 'temp';
        $sJunkTable = 'junk';
        $i = 1;

        $fkc = $this->db->conn->query('SET foreign_key_checks = 0');

        foreach ($aTableNames as $sTable)
        {
            $r1 = $this->db->conn->query('CREATE TABLE ' . ($sTempTable . $i) . ' LIKE ' . $sTable);
            $r2 = $this->db->conn->query('INSERT INTO ' . ($sTempTable . $i) . ' SELECT * FROM ' . $sTable . ' LIMIT ' . $this->numRows);
            $r3 = $this->db->conn->query('RENAME TABLE ' . $sTable . ' TO ' . ($sJunkTable . $i) . ', ' . ($sTempTable . $i) . ' TO ' . $sTable);
            $r4 = $this->db->conn->query('DROP TABLE ' . ($sJunkTable . $i));
                # name iterator used to avoid 'shadowing effect' on same table name and foreign key contraints

            if ($r1 && $r2 && $r3 && $r4)
            {
                $sMessage = 'Clipped table ' . $sTable . '.';
            }
            else
            {
                $sMessage = 'Table ' . $sTable . ' clipping failed.';
            }

            Logger::log($sMessage, true);

            $i++;
        }

        $fkc = $this->db->conn->query('SET foreign_key_checks = 1');
    }
}
