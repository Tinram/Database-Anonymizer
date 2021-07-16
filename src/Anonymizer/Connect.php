<?php

declare(strict_types=1);

namespace Anonymizer;

use mysqli;

final class Connect
{
    /**
        * Create MySQLi database connection.
        * Return MySQLi object of configuration connection.
        *
        * @author          Martin Latter
        * @author          Aaron Saray (getInstance())
        * @copyright       Martin Latter 2016
        * @version         0.04
        * @license         GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link            https://github.com/Tinram/Database-Anonymizer.git
        * @package         Anonymizer
    */

    use Logger;

    /** @var object $conn, mysqli connection */
    public $conn = null;

    /** @var string $dbname, database name used in queries */
    public $dbname = '';

    /** @var boolean $bActiveConnection, active connection */
    private $bActiveConnection = false;

    /** @var object $_instance, instance of class */
    private static $_instance = null;

    /**
        * Constructor: create MySQLi database object.
        *
        * @param   object $oConfig, configuration parameters
    */
    private function __construct(Configuration $oConfig)
    {
        $this->conn = new mysqli($oConfig::DB_HOST, $oConfig::DB_USERNAME, $oConfig::DB_PASSWORD, $oConfig::DB_NAME);

        if ($this->conn->connect_errno > 0)
        {
            $sMessage = 'Connection failed: ' . $this->conn->connect_error . ' (' . $this->conn->connect_errno . ')';
            Logger::log($sMessage, true);
            die($sMessage);
        }
        else
        {
            $this->bActiveConnection = true;
            $this->conn->set_charset($oConfig::DB_CHARSET);
            $this->dbname = $oConfig::DB_NAME;
        }
    }

    /**
        * Close DB connection on script termination.
    */
    public function __destruct()
    {
        if ($this->bActiveConnection)
        {
            $this->conn->close();
        }
    }

    /**
        * Public API.
        *
        * @param   object $oConfig, configuration parameters
        * @return  Connect, MySQLi connection
    */
    public static function getInstance(Configuration $oConfig): self
    {
        if ( ! self::$_instance instanceof self)
        {
            self::$_instance = new self($oConfig);
        }

        return self::$_instance;
    }
}
