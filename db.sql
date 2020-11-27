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
	`year` INTEGER NOT NULL,
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


INSERT INTO category(name) VALUES
('crime'), ('romance'), ('adventure'), ('fantasy'), ('horror'), ('kids');

INSERT INTO book (title, author, year, price, category) VALUES
('The dancing girls', 'M.M. Chouinard', 2019, 8.56, 1),
('Spilled milk', 'K.L. Randis', 2013, 11.30, 1),
('The pale-faced lie', 'David Crow', 2019, 13.09, 1),
('Smallbone Deceased', 'Michael Gilbert', 2019, 11.08, 1),
('The widow cabin', 'L.G. Davis', 2020, 14.99, 1),
('The perfect wife', 'Blake Pierce', 2018, 5.99, 1),
('High Achiever', 'Tiffany Jenkins', 2019, 9.42, 1),
('Above Suspicion', 'Joe Sharkey', 2017, 7.71, 1),
('Tears of the silenced', 'Misty Griffin', 2020, 14.05, 1),
('Girl last seen', 'Nina Laurin', 2017, 8.57, 1),
('It ends with us', 'Collen Hoover', 2016, 9.08, 2),
('That boy', 'Jillian Dodd', 2014, 11.14, 2),
('For once in my life', 'Collen Coleman', 2018, 8.57, 2),
('Crazy little thing', 'Tracy Brogan', 2012, 11.11, 2),
('It all comes back to you', 'Beth Duke', 2018, 7.14, 2),
('The hideaway', 'Lauren K. Denton', 2017, 23.47, 2),
('All the ugly and wonderful things', 'Bryn Greenwood', 2017, 13.71, 2),
('Cottage by the sea', 'Debbie Macomber', 2019, 24.01, 2),
('The return', 'Nicholas Sparks', 2020, 9.43, 2),
('The numbers game', 'Danielle Steel', 2020, 12.85, 2);


SELECT b.*
FROM `user` u INNER JOIN `order` o ON u.id = o.`user` INNER JOIN book b ON b.id = o.book
WHERE u.id = 'utente' AND o.payment_ok;

SELECT b.*
FROM book b INNER JOIN category c ON b.category = c.id
WHERE c.`name` = 'categoria';

SELECT * 
FROM `user` u INNER JOIN authtoken a ON u.id = a.`user`
WHERE a.id = 'authtoken';
