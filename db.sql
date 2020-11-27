Drop SCHEMA IF EXISTS ebookmarket;
CREATE SCHEMA ebookmarket ;

USE ebookmarket;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(32) NOT NULL,
	`email` VARCHAR(50) NOT NULL,
	`passwordhash` VARCHAR(255) NOT NULL,
	`valid` TINYINT NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY username (`username`),
	UNIQUE KEY email (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(30) NOT NULL,
	PRIMARY KEY (id),
    UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `book`;
CREATE TABLE `book` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(50) NOT NULL,
	`author` VARCHAR(30) NOT NULL,
	`date` INTEGER NOT NULL,
	`price` DECIMAL(10,2) NOT NULL,
    `category` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`) , 
    
    CONSTRAINT `category`
    FOREIGN KEY (`category`)
    REFERENCES `ebookmarket`.`category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
	`book` INT UNSIGNED NOT NULL,
    `user` INT UNSIGNED NOT NULL,
	`date` INTEGER NOT NULL,
	`payment_ok` TINYINT NOT NULL DEFAULT '0',
	PRIMARY KEY (`book`, `user`), 
    
    CONSTRAINT `book`
    FOREIGN KEY (`book`)
    REFERENCES `ebookmarket`.`book` (`id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE,
	
    CONSTRAINT `user1`
    FOREIGN KEY (`user`)
    REFERENCES `ebookmarket`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `authtoken`;
CREATE TABLE `authtoken` (
	`id` VARCHAR(32) NOT NULL,
	`expire_time` INTEGER NOT NULL,
	`type` ENUM ('PASSWORD_RECOVERY', 'AUTHENTICATION', 'VERIFY_MAIL') NOT NULL,
    `user` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`) , 
    
    CONSTRAINT `user2`
    FOREIGN KEY (`user`)
    REFERENCES `ebookmarket`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



SELECT b.*
FROM `user` u INNER JOIN `order` o ON u.id = o.`user` INNER JOIN book b ON b.id = o.book
WHERE u.id = 'utente';

SELECT b.*
FROM book b INNER JOIN category c ON b.category = c.id
WHERE c.`name` = 'categoria';

SELECT * 
FROM `user` u INNER JOIN authtoken a ON u.id = a.`user`
WHERE a.id = 'authtoken';
