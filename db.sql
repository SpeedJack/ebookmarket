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
('The Dancing Girls', 'M.M. Chouinard', 2019, 8.56, 1),
('Spilled Milk', 'K.L. Randis', 2013, 11.30, 1),
('The Pale-Faced Lie', 'David Crow', 2019, 13.09, 1),
('Smallbone Deceased', 'Michael Gilbert', 2019, 11.08, 1),
('The Widow Cabin', 'L.G. Davis', 2020, 14.99, 1),
('The Perfect Wife', 'Blake Pierce', 2018, 5.99, 1),
('High Achiever', 'Tiffany Jenkins', 2019, 9.42, 1),
('Above Suspicion', 'Joe Sharkey', 2017, 7.71, 1),
('Tears of the Silenced', 'Misty Griffin', 2020, 14.05, 1),
('Girl Last Seen', 'Nina Laurin', 2017, 8.57, 1),
('It Ends with Us', 'Collen Hoover', 2016, 9.08, 2),
('That Boy', 'Jillian Dodd', 2014, 11.14, 2),
('For Once in My Life', 'Collen Coleman', 2018, 8.57, 2),
('Crazy Little Thing', 'Tracy Brogan', 2012, 11.11, 2),
('It All Comes Back to You', 'Beth Duke', 2018, 7.14, 2),
('The Hideaway', 'Lauren K. Denton', 2017, 23.47, 2),
('All the Ugly and Wonderful Things', 'Bryn Greenwood', 2017, 13.71, 2),
('Cottage by the Sea', 'Debbie Macomber', 2019, 24.01, 2),
('The Return', 'Nicholas Sparks', 2020, 9.43, 2),
('The Numbers Game', 'Danielle Steel', 2020, 12.85, 2),
('The Hawk Awakening', 'Michal Yanof', 2020, 12.85, 3),
('Choppy water', 'Stuart Woods', 2020, 14.58, 3),
('The Summer of Chasing Dreams', 'Holly Martin', 2019, 8.57, 3),
('The Girl Who Lived', 'Cristopher Greyson', 2020, 17.11, 3),
('Prelude to Extinction', 'Andreas Karpf', 2019, 8.57, 3),
('What Happened at the Lake', 'Phil M. Williams', 2016, 11.40, 3),
('I Have a Bad Feeling About This', 'Jeff Strand', 2014, 6.34, 3),
('The Last Castle', 'Denise Kiernan', 2018, 12.00, 3),
('The Tuscan Secret', 'Angela Petch', 2019, 9.42, 3),
('I Am Watching You', 'Teresa Driscoll', 2017, 21.43, 3),
('Where the Forest Meets the Stars', 'Glendy Vanderah', 2019, 12.82, 4),
('Grows Up', 'Marian Keyes', 2020, 17.03, 4),
('The Water Dancer', 'Joe Morton', 2020, 12.00, 4),
('Harry Potter and the Sorcerer stone', 'J.K. Rowling', 1997, 18.77, 4),
('Harry Potter and the Chamber of Secrets', 'J.K. Rowling', 1998, 9.42, 4),
('Harry Potter and the Prisoner of Azkaban', 'J.K. Rowling', 1999, 9.42, 4),
('Harry Potter and the Goblet of Fire', 'J.K. Rowling', 2000, 11.14, 4),
('Harry Potter and the Order of The Phoenix', 'J.K. Rowling', 2003, 11.14, 4),
('Harry Potter and the Half-Blood Prince', 'J.K. Rowling', 2005, 11.14, 4),
('Harry Potter and the Deathly Hallows', 'J.K. Rowling', 2007, 12.85, 4),
('Madhouse', 'Miguel Estrada', 2017, 7.71, 5),
('If it Bleeds', 'Stephen King', 2020, 12.85, 5),
('Home Before Dark', 'Riley Sager', 2020, 12.86, 5),
('The Woman in the Window', 'A.J. Finn', 2019, 6.84, 5),
('White Rose, Black Forest', 'Eoin Dempsey', 2018, 21.00, 5),
('Best Seller', 'Susan May', 2018, 12.82, 5),
('Then She Was Gone', 'Lisa Jewell', 2018, 7.40, 5),
('Pax', 'Sara Pennypacker', 2017, 7.28, 5),
('Watchers', 'Dean Koontz', 2008, 8.57, 5),
('Verity', 'Colleen Hoover', 2018, 12.07, 5),
('Freddie The Farting Snowman', 'Jane Bexley', 2020, 10.19, 6),
('I Promise', 'LeBron James', 2020, 11.13, 6),
('Just Go to Bed', 'Mercer Mayer', 2001, 11.21, 6),
('All About Weather', 'Huda Harajli M.A.', 2010, 10.00, 6),
('The Wonky Donkey', 'Craig Smith', 2020, 10.19, 6),
('A Coronavirus Christmas', 'Shannon Jett', 2020, 9.42, 6),
('The Busy Little Squirrel', 'Nancy Tafuri', 2010, 6.42, 6),
('If Animals Kissed Good Night', 'Ann Whitfors Paul', 2014, 6.85, 6),
('Room on the Broom', 'Julia Donaldson', 2003, 4.29, 6),
('Where the Wild Things Are', 'Maurice Sendak', 2012, 6.43, 6);


/*
SELECT b.*
FROM `user` u INNER JOIN `order` o ON u.id = o.`user` INNER JOIN book b ON b.id = o.book
WHERE u.id = 'utente' AND o.payment_ok;

SELECT b.*
FROM book b INNER JOIN category c ON b.category = c.id
WHERE c.`name` = 'categoria';

SELECT * 
FROM `user` u INNER JOIN authtoken a ON u.id = a.`user`
WHERE a.id = 'authtoken';
*/