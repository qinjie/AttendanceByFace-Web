/*
SQLyog Ultimate v12.04 (64 bit)
MySQL - 10.1.10-MariaDB : Database - stud_attendance
*********************************************************************
*/
DROP DATABASE /*!32312 IF EXISTS*/`stud_attendance`;

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`stud_attendance` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `stud_attendance`;

/*Table structure for table `beacon` */

DROP TABLE IF EXISTS `beacon`;

CREATE TABLE `beacon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(32) NOT NULL,
  `major` varchar(10) DEFAULT NULL,
  `minor` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `beacon` */

/*Table structure for table `lesson` */

DROP TABLE IF EXISTS `lesson`;

CREATE TABLE `lesson` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `semester` varchar(10) DEFAULT NULL,
  `module_id` varchar(10) DEFAULT NULL,
  `subject_area` varchar(10) DEFAULT NULL,
  `catalog_number` varchar(10) DEFAULT NULL,
  `class_section` varchar(5) DEFAULT NULL,
  `component` varchar(5) DEFAULT NULL,
  `facility` varchar(15) DEFAULT NULL,
  `venue_id` int(10) unsigned DEFAULT NULL,
  `weekday` varchar(5) DEFAULT NULL,
  `start_time` varchar(10) DEFAULT NULL,
  `end_time` varchar(10) DEFAULT NULL,
  `meeting_patten` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `venue_id` (`venue_id`),
  CONSTRAINT `lesson_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

/*Data for the table `lesson` */

insert  into `lesson`(`id`,`semester`,`module_id`,`subject_area`,`catalog_number`,`class_section`,`component`,`facility`,`venue_id`,`weekday`,`start_time`,`end_time`,`meeting_patten`,`created_at`,`updated_at`) values 
  (1,NULL,'007685','ELECTRO','   1AMPR','L2L','LEC','05-03-0001',NULL,'MON','10:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (2,NULL,'007685','ELECTRO','   1AMPR','P2L1','PRA','46-01-0003',NULL,'THUR','15:00','17:00','ODD\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (3,NULL,'011197','ELECTRO','   2CPP2','T2L1','TUT','58-01-0002',NULL,'WED','10:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (4,NULL,'010152','ELECTRO','   1EGPHY','T2L1','TUT','06-03-0006',NULL,'TUES','15:00','16:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (5,NULL,'008045','ELECTRO','   1APPG','P2L1','PRA','05-02-0015',NULL,'FRI','10:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (6,NULL,'008045','ELECTRO','   1APPG','P2L1','PRA','08-06-0001',NULL,'TUES','13:00','15:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (7,NULL,'006492','ELECTRO','   1DEL','LL12','LEC','06-05-0001',NULL,'MON','15:00','17:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (8,NULL,'006492','ELECTRO','   1DEL','LL12','LEC','06-05-0001',NULL,'THUR','12:00','13:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (9,NULL,'006492','ELECTRO','   1DEL','P2L1','PRA','06-03-0004',NULL,'MON','08:00','10:00','EVEN\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (10,NULL,'006492','ELECTRO','   1DEL','T2L1','TUT','06-06-0006',NULL,'THUR','10:00','11:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (11,NULL,'009885','ELECTRO','   1EDPT1','P2L1','PRA','04-05-0001',NULL,'TUES','09:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (12,NULL,'010152','ELECTRO','   1EGPHY','L2L','LEC','04-02-0002',NULL,'THUR','08:00','10:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (13,NULL,'005696','IS MATH','   1EM3A','LL12','LEC','04-02-0002',NULL,'MON','13:00','15:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (14,NULL,'005696','IS MATH','   1EM3A','LL12','LEC','04-02-0002',NULL,'FRI','09:00','10:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (15,NULL,'005696','IS MATH','   1EM3A','T2L1','TUT','04-03-0007',NULL,'THUR','11:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (16,NULL,'010428','AE','  75INT6','PL23','PRA','',NULL,'WED','17:00','18:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (17,NULL,'007777','AE','   2FAT','LM12','LEC','08-04-0001',NULL,'WED','09:00','10:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (18,NULL,'007777','AE','   2FAT','T1M2','TUT','04-02-0008',NULL,'TUES','15:00','17:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (19,NULL,'006897','ELECTRIC','   2ELTECH','L1M2','LEC','04-03-0009',NULL,'WED','10:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (20,NULL,'006897','ELECTRIC','   2ELTECH','P1M2','PRA','06-06-0007',NULL,'TUES','13:00','15:00','EVEN\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (21,NULL,'006897','ELECTRIC','   2ELTECH','T1M2','TUT','05-05-0003',NULL,'FRI','11:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (22,NULL,'009882','ELECTRO','   1EMPTS','P1M2','PRA','04-05-0002',NULL,'FRI','13:00','16:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (23,NULL,'011196','ELECTRO','   2CPP1','T1M2','TUT','05-03-0009',NULL,'WED','13:00','15:00','EVEN\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (24,NULL,'010449','IS IE','   8INNOVA','T05','TUT','72-03-0015',NULL,'MON','13:00','15:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (25,NULL,'005383','IS MATH','   3EG2','T1M2','TUT','04-03-0010',NULL,'THUR','10:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (26,NULL,'005383','IS MATH','   3EG2','L1M2','LEC','06-04-0007',NULL,'THUR','15:00','16:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (27,NULL,'005383','IS MATH','   3EG2','L1M2','LEC','05-02-0009',NULL,'TUES','10:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (28,NULL,'009521','IS PDA','   7COMISS','T03','TUT','05-04-0009',NULL,'MON','08:00','12:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (29,NULL,'006898','MECHANIC','   2ENGMEC','P1M2','PRA','47-06-0005',NULL,'TUES','13:00','15:00','ODD\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (30,NULL,'006898','MECHANIC','   2ENGMEC','L1M2','LEC','06-06-0006',NULL,'THUR','13:00','15:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19'),
  (31,NULL,'006898','MECHANIC','   2ENGMEC','T1M2','TUT','04-03-0005',NULL,'FRI','10:00','11:00','\r','0000-00-00 00:00:00','2016-04-26 11:09:19');

/*Table structure for table `user` */

DROP TABLE IF EXISTS `user`;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '10',
  `role` smallint(6) NOT NULL DEFAULT '10',
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `user` */

insert  into `user`(`id`,`username`,`auth_key`,`password_hash`,`email`,`status`,`created_at`,`updated_at`,`name`,`role`) values 
  (1,'','cPbJFG-iAzqNRTLRZnJ-r_Suqa9vzkgT','$2y$13$2hu6q.PtQF5jplH930GS1OLgW.e1VOjK4UpTtTXxu3TaTXbwkgzDW','mark.qj@gmail.com',1,1445415998,1461941104,'Teacher',20),
  (15,NULL,'bMlOxgHdwTyLr3Nh3JI6StXz6SL0jOXE','$2y$13$upP/KvUhqRgqFv7AXQa8uuRD.XxqW2deMRU6IYdX9WavLYAI3ZL3a','parent@mail.ru',1,1447691728,1459412913,'Student',10),
  (18,NULL,'','$2y$13$SZVzEK9bqUSf4CDFW3cbK.glXDubG6XzDhVnq3seXvNsxEI8.8s5e','teacher@mail.ru',1,1447745333,1461926244,'Teacher',20),
  (23,NULL,'','$2y$13$/3TLZjGEkzg3VuksfqwUgetN58T/b3Vjp7vmklwryCXmlkPG2oLMa','manager@mail.ru',1,1460811289,1461926211,'Student',10),
  (52,NULL,'BP4dN0s5LU5OItOd4XnytTFnR5phJ5X_','$2y$13$R31I9.Ah7CKIjclBtyPPk.Bi.st9jZoh8yBbuKszkjgW4/C.WK7Yq','principle@mail.ru',1,1461214049,1461926225,'Student',10);

/*Table structure for table `student` */

DROP TABLE IF EXISTS `student`;

CREATE TABLE `student` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card` varchar(10) NOT NULL,
  `name` varchar(120) DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `acad` varchar(10) DEFAULT NULL,
  `uuid` varchar(40) DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `student_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user1` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

/*Data for the table `student` */

insert  into `student`(`id`,`card`,`name`,`gender`,`acad`,`uuid`,`user_id`,`created_at`,`updated_at`) values 
  (1,'AE','10164662A',NULL,'ADRIAN YOO',NULL,NULL,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
  (2,'AE','10157409D',NULL,'AIK YU CHE',NULL,NULL,'0000-00-00 00:00:00','2016-04-26 10:49:37'),
  (3,'AE','10169807E',NULL,'AKAASH SIN',NULL,NULL,'0000-00-00 00:00:00','2016-04-26 10:49:37');

/*Table structure for table `timetable` */

DROP TABLE IF EXISTS `timetable`;

CREATE TABLE `timetable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(10) unsigned NOT NULL,
  `lesson_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`,`lesson_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `timetable_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `timetable_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=latin1;

/*Data for the table `timetable` */

insert  into `timetable`(`id`,`student_id`,`lesson_id`,`created_at`) values 
  (32,1,1,'2016-04-26 11:10:06'),
  (33,1,2,'2016-04-26 11:10:06'),
  (34,1,3,'2016-04-26 11:10:06'),
  (35,1,4,'2016-04-26 11:10:06'),
  (36,1,5,'2016-04-26 11:10:06'),
  (37,1,6,'2016-04-26 11:10:06'),
  (38,1,7,'2016-04-26 11:10:06'),
  (39,1,8,'2016-04-26 11:10:06'),
  (40,1,9,'2016-04-26 11:10:06'),
  (41,1,10,'2016-04-26 11:10:06'),
  (42,1,11,'2016-04-26 11:10:06'),
  (43,1,12,'2016-04-26 11:10:06'),
  (44,1,13,'2016-04-26 11:10:06'),
  (45,1,14,'2016-04-26 11:10:06'),
  (46,1,15,'2016-04-26 11:10:06'),
  (47,1,16,'2016-04-26 11:10:06'),
  (48,2,17,'2016-04-26 11:10:06'),
  (49,3,18,'2016-04-26 11:10:06'),
  (50,3,19,'2016-04-26 11:10:06'),
  (51,3,20,'2016-04-26 11:10:06'),
  (52,3,21,'2016-04-26 11:10:06'),
  (53,3,22,'2016-04-26 11:10:06'),
  (54,3,23,'2016-04-26 11:10:06'),
  (55,3,24,'2016-04-26 11:10:06'),
  (56,3,25,'2016-04-26 11:10:06'),
  (57,3,26,'2016-04-26 11:10:06'),
  (58,3,27,'2016-04-26 11:10:06'),
  (59,3,28,'2016-04-26 11:10:06'),
  (60,3,29,'2016-04-26 11:10:06'),
  (61,3,30,'2016-04-26 11:10:06'),
  (62,3,31,'2016-04-26 11:10:06');

/*Table structure for table `user_token` */

DROP TABLE IF EXISTS `user_token`;

CREATE TABLE `user_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `expire_date` datetime NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_date` datetime NOT NULL,
  `action` smallint(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  CONSTRAINT `usertoken_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

/*Data for the table `user_token` */

insert  into `user_token`(`id`,`user_id`,`token`,`title`,`ip_address`,`expire_date`,`created_date`,`updated_date`,`action`) values 
  (2,15,'uEjx4gdvgBZmJbxEZfqG8E6Qs1H6c6nu','ACTION_CHANGE_EMAIL','127.0.0.1','2015-12-14 13:28:25','2015-12-07 13:28:05','2015-12-07 13:28:25',3),
  (4,15,'mHQk3giA-4jAl7NHHoeMWjXXyUC6Sf6e','ACTION_CHANGE_EMAIL','127.0.0.1','2015-12-14 18:04:33','2015-12-07 18:04:28','2015-12-07 18:04:33',3),
  (5,23,'H_4Ismh6tcr0JgLSJNohXN703lXn1WKv','ACTION_ACTIVATE_ACCOUNT','127.0.0.1','2015-12-15 21:51:58','2015-12-08 21:51:58','2015-12-08 21:51:58',1),
  (6,24,'NvuTHLloI-pOYjwuTHlzmQO5MIQl3T0N','ACTION_ACTIVATE_ACCOUNT','127.0.0.1','2015-12-15 21:53:50','2015-12-08 21:53:50','2015-12-08 21:53:50',1),
  (7,23,'ZItZpQugkTc6Z9ne5_UN6kFNM6lIjY2o','ACTION_ACTIVATE_ACCOUNT','127.0.0.1','2015-12-18 19:13:30','2015-12-11 19:13:30','2015-12-11 19:13:30',1),
  (8,18,'K3pnWmgdOMxo4Zx318vMKIeiq6Op9LXr','ACTION_RESET_PASSWORD','127.0.0.1','2016-04-02 19:10:36','2016-03-26 19:10:36','2016-03-26 19:10:36',2),
  (9,20,'FskuVth7ZFef-du2ZaoNJe6i21flTwSV','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 08:41:00','2016-04-16 08:41:00','2016-04-16 08:41:00',3),
  (10,20,'s5GCHQXtg_1weudrhbKExxh0erB-RbGs','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 08:43:16','2016-04-16 08:43:16','2016-04-16 08:43:16',3),
  (11,21,'XgnnkvXAL6-ugI3QA__bB3_e9oSaHDj0','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 08:50:38','2016-04-16 08:50:38','2016-04-16 08:50:38',3),
  (12,21,'85I2tLb-Xrx0cXER0Q8rdXdiaabe5y3Q','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 09:03:02','2016-04-16 09:03:02','2016-04-16 09:03:02',3),
  (13,21,'hgNTlkhX8pxLn6dVrVw2ri5o73VgY_C-','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 09:04:46','2016-04-16 09:04:46','2016-04-16 09:04:46',3),
  (14,21,'DmBc3C6DlXeiW7vu_czS23ndg6sTqgcL','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 09:05:29','2016-04-16 09:05:29','2016-04-16 09:05:29',3),
  (15,21,'AdgD-Tyx_fedkZ0GT4UlR5lGDQMoCZBz','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 09:08:11','2016-04-16 09:07:59','2016-04-16 09:08:11',3),
  (16,21,'uhrUFnYn0h4aoEWeilaWBO1rRlKADitz','ACTION_CHANGE_EMAIL','5.57.8.106','2016-04-23 09:09:45','2016-04-16 09:09:45','2016-04-16 09:09:45',3),
  (17,30,'RG8CRBk38whuzxZi4jDPLnnrI9SbxAN6','ACTION_ACTIVATE_ACCOUNT','77.95.61.49','2016-04-26 04:40:34','2016-04-19 04:40:12','2016-04-19 04:40:34',1),
  (18,35,'S6WCSc0RGlC6tBFoiAKF3nLZpcS2HKpa','ACTION_ACTIVATE_ACCOUNT','77.95.61.49','2016-04-26 05:07:31','2016-04-19 05:07:02','2016-04-19 05:07:31',1),
  (21,40,'FYWbNT57SwigP2PtZ_BKBLRz0Z-mcn1N','ACTION_ACTIVATE_ACCOUNT','178.217.174.2','2016-04-27 07:47:43','2016-04-20 07:47:43','2016-04-20 07:47:43',1),
  (22,42,'J5151vo8a5o4wVADvduHnU9KNcCn3kPy','ACTION_ACTIVATE_ACCOUNT','178.217.174.2','2016-04-27 09:33:27','2016-04-20 09:33:27','2016-04-20 09:33:27',1),
  (23,51,'PTrvVM--hRCxPDOcQC1Dweqzg5qmUvmB','ACTION_ACTIVATE_ACCOUNT','94.143.199.47','2016-04-28 04:46:11','2016-04-21 04:46:11','2016-04-21 04:46:11',1),
  (24,52,'fFTZnjgnZaWdulJYZ-un8JRX-yg-Q9_l','ACTION_ACTIVATE_ACCOUNT','94.143.199.47','2016-04-28 04:48:10','2016-04-21 04:47:29','2016-04-21 04:48:10',1);

/*Table structure for table `venue` */

DROP TABLE IF EXISTS `venue`;

CREATE TABLE `venue` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(20) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `venue` */

/*Table structure for table `venue_beacon` */

DROP TABLE IF EXISTS `venue_beacon`;

CREATE TABLE `venue_beacon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `venue_id` int(10) unsigned DEFAULT NULL,
  `beacon_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `venue_id` (`venue_id`),
  KEY `beacon_id` (`beacon_id`),
  CONSTRAINT `venue_beacon_ibfk_1` FOREIGN KEY (`venue_id`) REFERENCES `venue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `venue_beacon_ibfk_2` FOREIGN KEY (`beacon_id`) REFERENCES `beacon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `venue_beacon` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
