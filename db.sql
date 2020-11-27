USE ebookmarket;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(32) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`passwordhash` VARCHAR(255) NOT NULL,
	`valid` TINYINT NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`),
	UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
