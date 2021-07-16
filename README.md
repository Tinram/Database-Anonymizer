
# Database Anonymizer

#### Obliterate Personally Identifiable Information (PII) in MySQL / MariaDB databases.


## Purpose

### Primary

Obliterate sensitive personal data held in a database copy and / or exported logical backup files.

Sensitive personal data can be names, email addresses, telephone numbers, birthdays, and numerical identifiers. The selected personal data is destroyed to meet the requirements of data protection laws, GDPR regulations etc by overwriting with pseudo-data.

### Secondary

Reduce the size of backup files for development usage via database table truncation and clipping.


## Background

Some MySQL databases are huge and contain sensitive personal data.  
For development purposes, only a small subset of the database may be required.  
*Database Anonymizer* provides one way of creating a database export that is much smaller than the original, and with suitable configuration, contains no PII.


## Personal Data Colums

Data types recognised:

+ `CHAR, VARCHAR, TEXT`
+ `INT types`
+ `DECIMAL, FLOAT`
+ `DATE, DATETIME, TIMESTAMP`

Most personal data columns are likely to be `CHAR` and `VARCHAR` types.

The users table in the example schema *anon_test.sql* provides a reference.

`DECIMAL` AND `FLOAT` processing is basic (integer overwrites), but avoids using expensive function calls to generate the fractional part, and the resulting *.00* fraction shows the column data has been changed.

`ENUM`s are not supported.


## Usage

Set-up in *runner.php*

Anonymizer is reasonably modular.

If logging is not required, comment out the line:

```php
$log = Logger::setup($config);
```

and nothing will be logged in *src/Anonymizer/log/anon.log*

If tables do not require clipping (partial truncation) comment out the line:

```php
$c = new Clip($db, $numRows);
```


## Example Usage

```bash
$ mysql -h localhost -u root -p < src/Anonymizer/sql/anon_test.sql
```

Change any database credentials as needed in *src/Anonymizer/Configuration.php*

Fill the database with test data (use a tool such as Spawner, or a script such as [Database_Filler](https://github.com/Tinram/Database-Filler)) and create 150 rows of junk data for the next steps).

```sql
mysql> USE anon_test;
mysql> SELECT email FROM users;           # displays the default email column data
```

Configure *runner.php* or use the default parameters as they are, which specify:

```php
$tablesToTruncate = ['misc'];             # table called *misc* to be truncated (wiped, preserving table schema)
$numRows = 100;                           # number of rows to remain after clipping (reduced table rows, i.e. partial truncation)
$c = new Clip($db, $numRows);             # all database tables to be clipped
$anonymize = [ 'users' => ['email'] ];    # users.email column data will be anonymized/obliterated
```

```bash
    $ php runner.php                      # execute the anonymizer runner script
```

```sql
mysql> SELECT email FROM users;           # should now be 100 rows of pseudo email addresses

mysql> SELECT COUNT(*) FROM misc;         # should be 0, as table has been truncated

mysql> SELECT COUNT(*) FROM posts;        # should be 100, as table has been clipped
```

```bash
$ cat src/Anonymizer/log/anon.log         # shows the action log
```

    <timestamp> | Truncated table misc.
    <timestamp> | Clipped table misc.
    <timestamp> | Clipped table posts.
    <timestamp> | Clipped table users.
    <timestamp> | Filtered table users.

Create a database export of reduced data and anonymized email addresses:

```bash
$ mysqldump -h localhost -u root -p --single-transaction anon_test | gzip -9 > anon_test_reduced.sql.gz
```


## Other

Although probably obvious, only run this script on a database copy, not the master.

Tested on MySQL 5.7 and 8.0, and MariaDB 10.1 and 10.4


## License

Database Anonymizer is released under the [GPL v.3](https://www.gnu.org/licenses/gpl-3.0.html).
