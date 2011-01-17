# Sequel Pro dump
# Version 2492
# http://code.google.com/p/sequel-pro
#
# Host: localhost (MySQL 5.1.44)
# Database: ultralite
# Generation Time: 2011-01-15 10:29:09 -0600
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table pixelpost_posts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pixelpost_posts`;

CREATE TABLE `pixelpost_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL DEFAULT '1',
  `published` int(11) NOT NULL DEFAULT '1',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `slug` varchar(150) NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT 'Untitled',
  `description` text NOT NULL,
  `photo` text NOT NULL,
  `width` int(11) NOT NULL DEFAULT '0',
  `height` int(11) NOT NULL DEFAULT '0',
  `photo_t` text NOT NULL,
  `width_t` int(11) NOT NULL DEFAULT '0',
  `height_t` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author` (`author`),
  KEY `date` (`date`),
  KEY `published` (`published`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

LOCK TABLES `pixelpost_posts` WRITE;
/*!40000 ALTER TABLE `pixelpost_posts` DISABLE KEYS */;
INSERT INTO `pixelpost_posts` (`id`,`author`,`published`,`date`,`slug`,`title`,`description`,`photo`,`width`,`height`,`photo_t`,`width_t`,`height_t`)
VALUES
	(1,1,1,'2008-04-12 22:47:00','aint-it-kool','Ain\'t it KOOL','This image was taken in Memphis, Tn., while I was working on an assignment for my Photojournalism project at Western Kentucky University.  In truth, I wanted to go see the nightlife and put off my project...this image was taken while I was drinking at the bar.  Photo by: [Will Duncan](http://www.blinking8s.com/)','will-duncan_kool.jpg',650,433,'t_will-duncan_kool.jpg',100,75),
	(2,1,1,'2008-04-12 23:08:00','beams-of-light','Beams of Light','One has to admit that the Orlando Convention center has some beautiful architecture.  With a wide angle lens, it really conveys the full feeling of depth that one sees.    Photo by: [Jay Williams](http://photoblog.dview.us/)','jaywilliams_beams_of_light.jpg',650,433,'t_jaywilliams_beams_of_light.jpg',100,75),
	(3,1,1,'2008-04-12 23:18:00','street-lines','Street Lines','I am an avid fan of texture.  And there is no better place to find it than on a road.  Photo by: [Jay Williams](http://photoblog.dview.us/)','jaywilliams_street_lines.jpg',650,433,'t_jaywilliams_street_lines.jpg',100,75),
	(4,1,1,'2008-04-12 23:22:00','precious','P.R.E.C.I.O.U.S.','Wes Sandlin, 21, from Russellville, Ky. shows off his new knuckle tattoos, spelling P-R-E-C-I-O-U-S with black outline and pink filling. I saw them on a girl one time and just thought it would be funny\" said Sandlin.  Photo by: [Will Duncan](http://www.blinking8s.com/)\"','will-duncan_precious.jpg',650,431,'t_will-duncan_precious.jpg',100,75),
	(6,1,1,'2008-04-13 17:51:00','my-macbook-pro-and-me','My MacBook Pro and me','I got my MacBook Pro today! w00t  In a better world, everyone would use a Mac...  Photo by: [Catie Duncan](http://www.pixelpost.com/)','will-duncan_macbook_pro.jpg',650,433,'t_will-duncan_macbook_pro.jpg',100,75),
	(7,1,1,'2008-04-13 18:01:00','french-signs','French signs','French roadsigns in the afternoon sun.  Photo by: [Dennis Mooibroek](http://foto.schonhose.nl)','dennis_signs.jpg',650,433,'t_dennis_signs.jpg',100,75),
	(8,1,1,'2010-01-11 18:04:00','twenty-twos','Twenty-Twos','This image was taken during my typical afternoon walk last Thursday.  I have a weird fetish with the number 22 I think...  Photo By: [Will Duncan](http://www.blinking8s.com)','will-duncan_22.jpg',600,400,'t_will-duncan_22.jpg',100,75),
	(9,1,1,'2008-04-13 18:07:00','untitled-afternoon-light','Untitled afternoon light','Another shot from an afternoon walk.  The light is amazing in this state sometimes.  Photo by: [Will Duncan](http://www.blinking8s.com)','will-duncan_afternoon.jpg',650,433,'t_will-duncan_afternoon.jpg',100,75),
	(10,1,1,'2008-04-13 18:16:00','anti-bush-protest','Anti-Bush Protest','An anti-bush activists from the Washington DC area.  Photo by: [Will Duncan](http://www.blinking8s.com)','will-duncan_protest.jpg',650,431,'t_will-duncan_protest.jpg',100,75),
	(11,1,1,'2008-04-13 18:18:00','hands','Hands','This monkey didn\'t want you to watch him eat!  I shot this photo for a class assignment on hands\" and my teacher was not a fan. Please email him for his stupid opinion please.  Photo by: [Will Duncan](http://www.blinking8s.com)\"','will-duncan_hands.jpg',650,433,'t_will-duncan_hands.jpg',100,75),
	(12,1,1,'2008-04-13 19:03:00','skyscraper','גורד שחקים \"double\" \'single\' <Skyscraper>','During my short trip to Indianapolis, I made sure I set aside some time to go out and take photos of the city.  This one was one of my favorites.  Photo by: [Jay Williams](http://photoblog.dview.us/)\r\nSkyscraper in Hebrew is: גורד שחקים. Thanks to <a href=\"http://translate.google.com/\">Google Translate</a>.','jaywilliams_skyscraper.jpg',650,433,'t_jaywilliams_skyscraper.jpg',100,75),
	(13,1,1,'2012-12-12 12:12:12','future-post','Future-Post','This shouldn\'t be visible.','future-post.jpg',650,433,'t_future-post.jpg',100,75),
	(5,1,0,'2008-04-12 20:47:20','do-not-publish-this','Do Not Publish This','If you publish this, there will be consequences!','do_not_publish.jpg',650,433,'t_do_not_publish.jpg',100,75),
	(15,1,1,'2008-04-13 17:47:00','the-fat-suit','The fat suit','This photo was snagged at a party I was at last year. My friend John Doe came in a fat suit for some reason and I just couldnt resist making a photo to translate what I saw. It cracks me up...  Photo by: [Will Duncan](http://www.blinking8s.com/)','will-duncan_fatsuit.jpg',650,433,'t_will-duncan_fatsuit.jpg',100,75);

/*!40000 ALTER TABLE `pixelpost_posts` ENABLE KEYS */;
UNLOCK TABLES;





/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
