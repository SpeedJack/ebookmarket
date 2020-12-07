USE ebookmarket;

SET FOREIGN_KEY_CHECKS=0;

LOCK TABLES `users` WRITE;

INSERT INTO `users`(`id`, `username`, `email`, `passwordhash`, `valid`)
VALUES (1, 'user', 'user@example.com', '$2y$10$QEZuorXml.sqP7RrCOEJWek9uG.poXaKLMehiOV8BTatw/cM0hmv.', 1);

UNLOCK TABLES;

LOCK TABLES `categories` WRITE;

TRUNCATE TABLE `categories`;

INSERT INTO `categories`(`id`, `name`) VALUES
(1, 'Crime Fiction'), (2, 'Romance'), (3, 'Adventure'), (4, 'Fantasy'), (5, 'Horror'), (6, 'Kids');

UNLOCK TABLES;

LOCK TABLES `books` WRITE;

TRUNCATE TABLE `books`;

INSERT INTO `books` (`id`, `title`, `author`, `pubdate`, `price`, `filehandle`, `categoryid`) VALUES
(1, 'The Dancing Girls', 'M.M. Chouinard', '2019-05-17', 8.56, 'the-dancing-girls', 1),
(2, 'Spilled Milk', 'K.L. Randis', '2013-06-07', 11.30, 'spilled-milk', 1),
(3, 'The Pale-Faced Lie', 'David Crow', '2019-05-07', 13.09, 'the-pale-faced-lie', 1),
(4, 'Smallbone Deceased: A London Mistery', 'Michael Gilbert', '2019-01-22', 11.08, 'smallbone-deceased-a-london-mistery', 1),
(5, 'The Widow''s Cabin', 'L.G. Davis', '2020-09-05', 14.99, 'the-widows-cabin', 1),
(6, 'The Perfect Wife', 'Blake Pierce', '2020-07-28', 5.99, 'the-perfect-wife', 1),
(7, 'High Achiever', 'Tiffany Jenkins', '2019-06-18', 9.42, 'high-achiever', 1),
(8, 'Above Suspicion', 'Joe Sharkey', '2017-01-17', 7.71, 'above-suspicion', 1),
(9, 'Tears of the Silenced', 'Misty Griffin', '2019-01-17', 14.05, 'tears-of-the-silenced', 1),
(10, 'Girl Last Seen', 'Nina Laurin', '2017-06-20', 8.57, 'girl-last-seen', 1),
(11, 'It Ends with Us', 'Collen Hoover', '2016-08-02', 9.08, 'it-ends-with-us', 2),
(12, 'That Boy', 'Jillian Dodd', '2014-01-13', 11.14, 'that-boy', 2),
(13, 'For Once in My Life', 'Collen Coleman', '2018-11-20', 8.57, 'for-once-in-my-life', 2),
(14, 'Crazy Little Thing', 'Tracy Brogan', '2012-10-23', 11.11, 'crazy-little-thing', 2),
(15, 'It All Comes Back to You', 'Beth Duke', '2018-08-28', 7.14, 'it-all-comes-back-to-you', 2),
(16, 'The Hideaway', 'Lauren K. Denton', '2017-04-11', 23.47, 'the-hideaway', 2),
(17, 'All the Ugly and Wonderful Things', 'Bryn Greenwood', '2017-10-03', 13.71, 'all-the-ugly-and-wonderful-things', 2),
(18, 'Cottage by the Sea', 'Debbie Macomber', '2019-06-18', 24.01, 'cottage-by-the-sea', 2),
(19, 'The Return', 'Nicholas Sparks', '2020-09-29', 9.43, 'the-return', 2),
(20, 'The Numbers Game', 'Danielle Steel', '2020-03-03', 12.85, 'the-numbers-game', 2),
(21, 'The Hawk''s Awakening', 'Michal Yanof', '2020-11-25', 12.85, 'the-hawks-awakening', 3),
(22, 'Choppy water', 'Stuart Woods', '2020-08-11', 14.58, 'choppy-water', 3),
(23, 'The Summer of Chasing Dreams', 'Holly Martin', '2019-02-23', 8.57, 'the-summer-of-chasing-dreams', 3),
(24, 'The Girl Who Lived', 'Cristopher Greyson', '2017-12-11', 17.11, 'the-girl-who-lived', 3),
(25, 'Prelude to Extinction', 'Andreas Karpf', '2019-09-03', 8.57, 'prelude-to-extinction', 3),
(26, 'What Happened at the Lake', 'Phil M. Williams', '2018-03-16', 11.40, 'what-happened-at-the-lake', 3),
(27, 'I Have a Bad Feeling About This', 'Jeff Strand', '2014-03-01', 6.34, 'i-have-a-bad-feeling-about-this', 3),
(28, 'The Last Castle', 'Denise Kiernan', '2018-05-01', 12.00, 'the-last-castle', 3),
(29, 'The Tuscan Secret', 'Angela Petch', '2019-06-26', 9.42, 'the-tuscan-secret', 3),
(30, 'I Am Watching You', 'Teresa Driscoll', '2017-10-01', 21.43, 'i-am-watching-you', 3),
(31, 'Where the Forest Meets the Stars', 'Glendy Vanderah', '2019-03-01', 12.82, 'where-the-forest-meets-the-stars', 4),
(32, 'Grown Ups', 'Marian Keyes', '2020-02-06', 17.03, 'grown-ups', 4),
(33, 'The Water Dancer', 'TA-Nehisi Coates', '2020-11-17', 12.00, 'the-water-dancer', 4),
(34, 'Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', '1997-06-26', 18.77, 'harry-potter-1', 4),
(35, 'Harry Potter and the Chamber of Secrets', 'J.K. Rowling', '1998-07-02', 9.42, 'harry-potter-2', 4),
(36, 'Harry Potter and the Prisoner of Azkaban', 'J.K. Rowling', '1999-07-08', 9.42, 'harry-potter-3', 4),
(37, 'Harry Potter and the Goblet of Fire', 'J.K. Rowling', '2020-07-08', 11.14, 'harry-potter-4', 4),
(38, 'Harry Potter and the Order of The Phoenix', 'J.K. Rowling', '2003-06-21', 11.14, 'harry-potter-5', 4),
(39, 'Harry Potter and the Half-Blood Prince', 'J.K. Rowling', '2005-07-16', 11.14, 'harry-potter-6', 4),
(40, 'Harry Potter and the Deathly Hallows', 'J.K. Rowling', '2007-07-21', 12.85, 'harry-potter-7', 4),
(41, 'Madhouse', 'Miguel Estrada', '2017-10-24', 7.71, 'madhouse', 5),
(42, 'If it Bleeds', 'Stephen King', '2020-04-20', 12.85, 'if-it-bleeds', 5),
(43, 'Home Before Dark', 'Riley Sager', '2020-06-30', 12.86, 'home-before-dark', 5),
(44, 'The Woman in the Window', 'A.J. Finn', '2019-03-05', 6.84, 'the-woman-in-the-window', 5),
(45, 'White Rose, Black Forest', 'Eoin Dempsey', '2018-03-01', 21.00, 'white-rose-black-forest', 5),
(46, 'Best Seller', 'Susan May', '2018-03-09', 12.82, 'best-sellers', 5),
(47, 'Then She Was Gone', 'Lisa Jewell', '2018-11-06', 7.40, 'then-she-was-gone', 5),
(48, 'Pax', 'Sara Pennypacker', '2019-04-02', 7.28, 'pax', 5),
(49, 'Watchers', 'Dean Koontz', '2008-05-08', 8.57, 'watchers', 5),
(50, 'Verity', 'Colleen Hoover', '2018-12-10', 12.07, 'verity', 5),
(51, 'Freddie The Farting Snowman', 'Jane Bexley', '2020-10-27', 10.19, 'freddie-the-farting-snowman', 6),
(52, 'I Promise', 'LeBron James', '2020-08-11', 11.13, 'i-promise', 6),
(53, 'Just Go to Bed', 'Mercer Mayer', '2001-04-23', 11.21, 'just-go-to-bed', 6),
(54, 'All About Weather', 'Huda Harajli M.A.', '2020-03-24', 10.00, 'all-about-weather', 6),
(55, 'The Wonky Donkey', 'Craig Smith', '2010-05-01', 10.19, 'the-wonky-donkey', 6),
(56, 'A Coronavirus Christmas', 'Shannon Jett', '2020-10-12', 9.42, 'a-coronavirus-christmas', 6),
(57, 'The Busy Little Squirrel', 'Nancy Tafuri', '2010-08-03', 6.42, 'the-busy-little-squirrel', 6),
(58, 'If Animals Kissed Good Night', 'Ann Whitfors Paul', '2014-06-03', 6.85, 'if-animals-kissed-good-night', 6),
(59, 'Room on the Broom', 'Julia Donaldson', '2003-08-25', 4.29, 'room-on-the-broom', 6),
(60, 'Where the Wild Things Are', 'Maurice Sendak', '2012-12-26', 6.43, 'where-the-wild-things-are', 6);

UNLOCK TABLES;

SET FOREIGN_KEY_CHECKS=1;
