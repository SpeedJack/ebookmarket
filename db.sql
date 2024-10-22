CREATE DATABASE IF NOT EXISTS ebookmarket;
USE ebookmarket;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(32) NOT NULL,
	`email` VARCHAR(254) NOT NULL,
	`passwordhash` VARCHAR(255) NOT NULL,
	`valid` TINYINT NOT NULL DEFAULT '0',
	`remainingattempts` INT UNSIGNED NOT NULL DEFAULT 5,
	`lastattempt` INT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY username (`username`),
	UNIQUE KEY email (`email`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30) NOT NULL,
	PRIMARY KEY (id),
	UNIQUE KEY name (`name`)
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(255) NOT NULL,
	`author` VARCHAR(64) NOT NULL,
	`pubdate` DATE NOT NULL,
	`price` DECIMAL(10,2) NOT NULL,
	`filehandle` VARCHAR(255) NOT NULL,
	`categoryid` INT UNSIGNED,
	PRIMARY KEY (`id`),
	UNIQUE KEY (`filehandle`),
	KEY (`title`),
	KEY (`author`),
	FOREIGN KEY (`categoryid`)
		REFERENCES `categories` (`id`)
		ON DELETE SET NULL
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`userid` INT UNSIGNED NOT NULL,
	`bookid` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	KEY (`userid`),
	FOREIGN KEY (`bookid`)
		REFERENCES `books` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY (`userid`)
		REFERENCES `users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS `tokens`;
CREATE TABLE `tokens` (
	`id` CHAR(16) NOT NULL,
	`token` VARCHAR(255) NOT NULL,
	`userid` INT UNSIGNED,
	`bookid` INT UNSIGNED,
	`type` ENUM ('SESSION', 'VERIFY', 'RECOVERY', 'CSRF', 'BUYSTEP1', 'BUYSTEP2') NOT NULL,
	`expiretime` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`userid`)
		REFERENCES `users` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
	FOREIGN KEY (`bookid`)
		REFERENCES `books` (`id`)
		ON DELETE CASCADE
		ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

DROP EVENT IF EXISTS DeleteExpiredTokens;

CREATE EVENT DeleteExpiredTokens
	ON SCHEDULE EVERY 1 DAY
	DO
		DELETE FROM tokens WHERE expiretime <= UNIX_TIMESTAMP(CURRENT_TIMESTAMP);
