#!/usr/bin/env php
<?php

/**
    * Example to set-up and call Anonymizer
    * Martin Latter, 04/07/2021
*/

declare(strict_types=1);

use Anonymizer\Configuration;
use Anonymizer\Logger;
use Anonymizer\Connect;
use Anonymizer\Truncate;
use Anonymizer\Clip;
use Anonymizer\Filter;
use Anonymizer\CharGenerator;

require 'src/autoloader.php';


## INIT

$config = new Configuration();          # Configuration class contains database credentials
$log = Logger::setup($config);          # toggle logging to file
$db = Connect::getInstance($config);    # database connection object


## DATABASE ACTIONS

$numRows = 100;                         # number of rows to remain in clipped tables

$tablesToTruncate = ['misc'];           # array of table names to be truncated

$anonymize =
[
    # table columns in which to obliterate personal data
    # table_name => [column1, column2 ...]

    'users' => ['email']
    // 'users' => ['first_name','last_name','birthday', 'email','telephone','SSN','password_bcrypt','description','bio','cost','amount','active','added','upd']
];

# truncate large tables specified in $tablesToTruncate (comment out to skip this action)
$t = new Truncate($db, $tablesToTruncate);

# clip ALL database table rows to value of $numRows (comment out to skip this action)
$c = new Clip($db, $numRows);

# filter/anonymize personal data columns contained in $anonymize array
$f = new Filter($db, $anonymize, $numRows);