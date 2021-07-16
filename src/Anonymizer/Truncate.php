<?php

declare(strict_types=1);

namespace Anonymizer;

final class Truncate
{
    /**
        * Truncate tables.
        *
        * @author          Martin Latter
        * @copyright       Martin Latter 04/07/2021
        * @version         0.03
        * @license         GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link            https://github.com/Tinram/Database-Anonymizer.git
        * @package         Anonymizer
    */

    use Logger;

    /**
        * @param   object $oDB
        * @param   array<string> $aTables, table names
    */
    public function __construct(Connect $oDB, array $aTables)
    {
        $this->db = $oDB;

        if (count($aTables) !== 0)
        {
            $this->truncateTables($aTables);
        }
    }

    /**
        * Truncate (empty but preserve structure) the table names passed.
        *
        * @param   array<string> $aTables, table names
        * @return  void
    */
    private function truncateTables(array $aTables): void
    {
        $bError = false;

        foreach ($aTables as $sTable)
        {
            $rResult = $this->db->conn->query('TRUNCATE TABLE ' . $sTable);

            $sMessage = ($rResult === true) ?
                'Truncated table ' . $sTable . '.' :
                'TRUNCATION operation on table ' . $sTable . ' failed.';

            Logger::log($sMessage, true);
        }
    }
}
