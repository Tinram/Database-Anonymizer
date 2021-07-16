<?php

declare(strict_types=1);

namespace Anonymizer;

trait Logger
{
    /**
        * Logger used by classes.
        *
        * @author          Martin Latter
        * @copyright       Martin Latter 06/07/2021
        * @version         0.01
        * @license         GNU GPL version 3.0 (GPL v3); http://www.gnu.org/licenses/gpl.html
        * @link            https://github.com/Tinram/Database-Anonymizer.git
        * @package         Anonymizer
    */

    /** @var string $sLogDir, log file directory */
    private static $sLogDir;

    /** @var string $sLogFile, log file name */
    private static $sLogFile;

    /** @var boolean $bLogging, log toggle */
    private static $bLogging = false;

    /**
        * @param   object $oConfig, configuration parameters
    */
    public static function setup(Configuration $oConfig)
    {
        self::$sLogDir = $oConfig::LOG_DIR;
        self::$sLogFile = $oConfig::LOG_FILE;
        self::$bLogging = true;
    }

    /**
        * @param   string $message, message to log to file
        * @param   boolean $timestamp, toggle to include timestamp or not
        * @return  void
    */
    public static function log(string $message = '', bool $bTimestamp = false): void
    {
        /* if $bLogging initiation not called, don't log */
        if (self::$bLogging === false)
        {
            return;
        }

        if (empty($message))
        {
            return;
        }

        if ( ! file_exists(self::$sLogDir))
        {
            mkdir(self::$sLogDir);
        }

        if ( ! file_exists(self::$sLogFile))
        {
            $f = touch(self::$sLogDir . self::$sLogFile);

            if ($f === false)
            {
                echo 'Could not create log file ... ' . PHP_EOL;
            }
        }

        if ($bTimestamp === true)
        {
            $message = self::getTimestamp() . ' | ' . $message;
        }

        $logWrite = file_put_contents(self::$sLogDir . self::$sLogFile, $message . PHP_EOL, FILE_APPEND);

        if ($logWrite === false)
        {
            echo 'Could not write to log file ... check file permissions.' . PHP_EOL;
        }
    }

    /**
        * Return a timestamp with a custom format.
        *
        * @return  string
    */
    private static function getTimestamp(): string
    {
        return date('Y-m-d H:i:s P T');
    }
}
