<?php

declare(strict_types=1);

namespace Anonymizer;

final class Configuration
{
    /** @const DB_HOST */
    public const DB_HOST = 'localhost';

    /** @const DB_USERNAME */
    public const DB_USERNAME = 'anon';

    /** @const DB_PASSWORD */
    public const DB_PASSWORD = 'P@55w0rd';

    /** @const DB_NAME */
    public const DB_NAME = 'anon_test';

    /** @const DB_CHARSET */
    public const DB_CHARSET = 'utf8';

    /** @const LOG_DIR, log file directory */
    public const LOG_DIR = 'src/Anonymizer/log/';

    /** @const LOG_FILE */
    public const LOG_FILE = 'anon.log';
}
