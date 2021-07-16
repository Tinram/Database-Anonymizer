
CREATE DATABASE IF NOT EXISTS anon_test CHARSET=utf8mb4;

CREATE USER IF NOT EXISTS 'anon'@'localhost' IDENTIFIED BY 'P@55w0rd';
GRANT SELECT, UPDATE, INSERT, DELETE, CREATE, ALTER, DROP ON anon_test.* TO 'anon'@'localhost';
    -- ALTER USER 'anon'@'localhost' IDENTIFIED WITH mysql_native_password BY 'P@55w0rd'; # PHP < v.7.4 conflict with MySQL 8 default caching_sha2_password
FLUSH PRIVILEGES;

USE anon_test;


CREATE TABLE `users`
(
    `user_id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name`         VARCHAR(35) NOT NULL,
    `last_name`          VARCHAR(35) NOT NULL,
    `birthday`           DATE NOT NULL,
    `email`              VARCHAR(50) NOT NULL DEFAULT '',
    `telephone`          VARCHAR(20) NOT NULL DEFAULT '',
    `SSN`                CHAR(11) NOT NULL,
    `password_bcrypt`    CHAR(60) NOT NULL,
    `description`        VARCHAR(255) NOT NULL DEFAULT '',
    `bio`                TEXT NOT NULL,
    `cost`               DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
    `amount`             FLOAT NOT NULL DEFAULT 0.00,
    `active`             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `added`              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `upd`                DATETIME DEFAULT NULL,

    UNIQUE KEY `uidx_email` (`email`),
    PRIMARY KEY (`user_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'table to have column data anonymized';


CREATE TABLE `posts`
(
    `post_id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`            INT UNSIGNED NOT NULL,
    `body`               VARCHAR(255) NOT NULL DEFAULT '',

    FOREIGN KEY (`user_id`) REFERENCES users (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    PRIMARY KEY (`post_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'table to be clipped';


CREATE TABLE `misc`
(
    `misc_id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `info`               VARCHAR(50) NOT NULL DEFAULT '',

    PRIMARY KEY (`misc_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT 'table to be truncated';
