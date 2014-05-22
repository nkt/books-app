DROP TABLE IF EXISTS `act_books`;

CREATE TABLE `act_books` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `act_id` int(11) unsigned NOT NULL,
  `book_id` int(11) unsigned NOT NULL,
  `count` int(11) unsigned NOT NULL,
  `price` double unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_book_in_act` (`act_id`,`book_id`),
  KEY `book_id` (`book_id`),
  KEY `act_id` (`act_id`),
  CONSTRAINT `act_books_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  CONSTRAINT `act_books_ibfk_2` FOREIGN KEY (`act_id`) REFERENCES `acts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DELIMITER ;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `set_price_on_insert` BEFORE INSERT ON `act_books` FOR EACH ROW SET NEW.price = (SELECT price FROM books WHERE id = NEW.book_id) */;;
/*!50003 SET SESSION SQL_MODE="STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION" */;;
/*!50003 CREATE */ /*!50017 DEFINER=`root`@`localhost` */ /*!50003 TRIGGER `fire_books_on_insert` AFTER INSERT ON `act_books` FOR EACH ROW UPDATE books SET count = count - NEW.count WHERE id = NEW.book_id */;;
DELIMITER ;
/*!50003 SET SESSION SQL_MODE=@OLD_SQL_MODE */;

DROP TABLE IF EXISTS `acts`;

CREATE TABLE `acts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reason` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `authors`;

CREATE TABLE `authors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `books`;

CREATE TABLE `books` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` double unsigned NOT NULL,
  `count` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
