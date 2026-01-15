-- MySQL dump 10.13  Distrib 8.0.41, for macos15 (x86_64)
--
-- Host: localhost    Database: hms
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_trails`
--

DROP TABLE IF EXISTS `audit_trails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_trails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_trails_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `audit_trails_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `audit_trails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_trails`
--

LOCK TABLES `audit_trails` WRITE;
/*!40000 ALTER TABLE `audit_trails` DISABLE KEYS */;
INSERT INTO `audit_trails` VALUES (1,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #10 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:29:57','2026-01-02 05:29:57'),(2,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #11 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:30:16','2026-01-02 05:30:16'),(3,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #12 created for Test User','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:32:19','2026-01-02 05:32:19'),(4,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #13 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:34:16','2026-01-02 05:34:16'),(5,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #14 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:44:35','2026-01-02 05:44:35'),(6,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #15 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:44:48','2026-01-02 05:44:48'),(7,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #16 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:45:57','2026-01-02 05:45:57'),(8,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #17 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:49:58','2026-01-02 05:49:58'),(9,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #18 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:50:22','2026-01-02 05:50:22'),(10,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #19 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:50:28','2026-01-02 05:50:28'),(11,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #20 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:50:44','2026-01-02 05:50:44'),(12,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #21 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:50:51','2026-01-02 05:50:51'),(13,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #22 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:54:53','2026-01-02 05:54:53'),(14,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #23 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:55:02','2026-01-02 05:55:02'),(15,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #24 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:55:11','2026-01-02 05:55:11'),(16,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 1 assigned to Stay #24','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 05:55:11','2026-01-02 05:55:11'),(17,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #12','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 06:05:42','2026-01-02 06:05:42'),(18,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #25 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 06:14:43','2026-01-02 06:14:43'),(19,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 2 assigned to Stay #25','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 06:14:43','2026-01-02 06:14:43'),(20,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #9','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 14:56:49','2026-01-02 14:56:49'),(21,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #8','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 15:36:56','2026-01-02 15:36:56'),(22,2,'check_out',NULL,NULL,NULL,NULL,'Guest checked out from Stay #8 (status changed from checked_in to checked_out)','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 16:13:39','2026-01-02 16:13:39'),(23,2,'check_out',NULL,NULL,NULL,NULL,'Guest checked out from Stay #9 (status changed from checked_in to checked_out)','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 16:20:33','2026-01-02 16:20:33'),(24,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #16','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 16:45:49','2026-01-02 16:45:49'),(25,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #26 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 16:54:33','2026-01-02 16:54:33'),(26,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #27 created for Dennis Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 16:54:42','2026-01-02 16:54:42'),(27,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 2 assigned to Stay #27','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 16:54:42','2026-01-02 16:54:42'),(28,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #24','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 17:07:21','2026-01-02 17:07:21'),(29,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #23','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-02 17:11:00','2026-01-02 17:11:00'),(30,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #25','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 06:37:33','2026-01-03 06:37:33'),(31,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #27','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 07:42:52','2026-01-03 07:42:52'),(33,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #29 created for Dennisdstdfuiopj Kibet','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 08:24:45','2026-01-03 08:24:45'),(34,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 2 assigned to Stay #29 (Main)','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 08:24:45','2026-01-03 08:24:45'),(35,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 1 assigned to Stay #29 (Main)','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 08:24:45','2026-01-03 08:24:45'),(38,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #32 created for Gideon Boot Kidian','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 10:28:21','2026-01-03 10:28:21'),(39,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 2 assigned to Stay #32 (Main)','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 10:28:21','2026-01-03 10:28:21'),(40,2,'check_in',NULL,NULL,NULL,NULL,'Guest checked in for Stay #32','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-03 12:00:45','2026-01-03 12:00:45'),(41,2,'stay_created',NULL,NULL,NULL,NULL,'Stay #33 created for New Guest','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-04 10:32:03','2026-01-04 10:32:03'),(42,2,'room_assigned',NULL,NULL,NULL,NULL,'Room 1 assigned to Stay #33 (My wife has an issue with hotel soaPs, can we know what brand before hand?)','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','2026-01-04 10:32:03','2026-01-04 10:32:03');
/*!40000 ALTER TABLE `audit_trails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel_cache_system_services','a:6:{i:0;a:3:{s:5:\"title\";s:11:\"Travel Plan\";s:11:\"description\";s:78:\"We create custom travel plans to make your trip stress-free and unforgettable.\";s:4:\"icon\";s:20:\"flaticon-036-parking\";}i:1;a:3:{s:5:\"title\";s:16:\"Catering Service\";s:11:\"description\";s:70:\"Delicious food and beverages for your events, meetings, or hotel stay.\";s:4:\"icon\";s:19:\"flaticon-033-dinner\";}i:2;a:3:{s:5:\"title\";s:11:\"Babysitting\";s:11:\"description\";s:73:\"Professional and caring babysitting services to look after your children.\";s:4:\"icon\";s:16:\"flaticon-026-bed\";}i:3;a:3:{s:5:\"title\";s:7:\"Laundry\";s:11:\"description\";s:62:\"Quick and reliable laundry service to keep your clothes fresh.\";s:4:\"icon\";s:18:\"flaticon-024-towel\";}i:4;a:3:{s:5:\"title\";s:11:\"Hire Driver\";s:11:\"description\";s:73:\"Professional drivers to make your travel around the city smooth and safe.\";s:4:\"icon\";s:20:\"flaticon-044-clock-1\";}i:5;a:3:{s:5:\"title\";s:11:\"Bar & Drink\";s:11:\"description\";s:57:\"A variety of drinks and cocktails to enjoy your evenings.\";s:4:\"icon\";s:21:\"flaticon-012-cocktail\";}}',2083007426),('laravel_cache_system_settings','O:17:\"App\\Models\\System\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:6:\"system\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:32:{s:2:\"id\";i:1;s:4:\"name\";s:16:\"The Willis Hotel\";s:4:\"logo\";s:20:\"images/logo/logo.svg\";s:9:\"logo_icon\";s:25:\"images/logo/logo-icon.svg\";s:9:\"logo_dark\";s:25:\"images/logo/logo-dark.svg\";s:7:\"favicon\";s:23:\"images/logo/favicon.ico\";s:6:\"slogan\";s:228:\"Conveniently located along Sotik - Narok Highway with easy access to both Maasai Mara Game Reserve and the culture-filled Kalenjin and Maasai communities, we offer the perfect choice hotel for both business and leisure traveler.\";s:7:\"socials\";s:490:\"[{\"icon\": \"fab fa-facebook-f\", \"link\": \"https://www.facebook.com/Willishotel/\", \"name\": \"facebook\"}, {\"icon\": \"fab fa-twitter\", \"link\": \"https://x.com/thewillishotel?lang=en\", \"name\": \"x\"}, {\"icon\": \"fab fa-instagram\", \"link\": \"https://www.instagram.com/the_willis_hotel/?hl=en\", \"name\": \"instagram\"}, {\"icon\": \"fab fa-tripadvisor\", \"link\": \"https://www.tripadvisor.com/Hotel_Review-g7701883-d23330748-Reviews-The_Willis_Hotel_Bomet-Bomet_Rift_Valley_Province.html\", \"name\": \"tripadvisor\"}]\";s:8:\"timezone\";s:14:\"Africa/Nairobi\";s:11:\"date_format\";s:5:\"d/m/Y\";s:11:\"time_format\";s:5:\"H:i:s\";s:8:\"currency\";s:3:\"KES\";s:8:\"about_us\";s:696:\"{\"extra\": \"So when it comes to booking the perfect hotel, vacation rental, resort, apartment, guest house, or tree house, we’ve got you covered.\", \"title\": \"About Us\", \"subtitle\": \"South Rift\'s Finest\", \"description\": \"Nearness to the lake region counties which includes Narok county, Kericho, Kisii, Migori, Homabay and Kisumu counties respectively. In addition, it’s a gate way to the neighbouring countries of Uganda and Tanzania. It’s also a gateway to the Mau forest and the Tea growing highlands of both Bomet and Kericho counties. It’s within the Maasai Mara tourist circuit and a link to the North Rift of Nakuru and Eldoret tourist sites and hospitality industry in the region.\"}\";s:15:\"currency_symbol\";s:3:\"KES\";s:13:\"primary_color\";s:7:\"#ff8c00\";s:15:\"secondary_color\";s:7:\"#000000\";s:13:\"contact_email\";s:24:\"hotelthewillis@gmail.com\";s:13:\"contact_phone\";s:12:\"0758 000 010\";s:7:\"address\";s:30:\"P.O Box 669-20400 Bomet, Kenya\";s:16:\"meta_description\";N;s:13:\"meta_keywords\";N;s:12:\"facebook_url\";s:37:\"https://www.facebook.com/Willishotel/\";s:11:\"twitter_url\";s:36:\"https://x.com/thewillishotel?lang=en\";s:13:\"instagram_url\";s:50:\"https://www.instagram.com/the_willis_hotel/?hl=en|\";s:12:\"linkedin_url\";N;s:16:\"maintenance_mode\";i:1;s:16:\"pagination_limit\";i:15;s:10:\"custom_css\";N;s:9:\"custom_js\";N;s:8:\"settings\";s:422:\"{\"backup\": {\"auto_backup\": true, \"backup_to_cloud\": false, \"backup_frequency\": \"daily\", \"backup_retention\": 30}, \"security\": {\"login_attempts\": \"5\", \"password_expiry\": 90, \"session_timeout\": \"30\", \"two_factor_auth\": false}, \"integrations\": {\"google_maps_key\": \"\", \"google_analytics\": null}, \"notifications\": {\"sms_notifications\": false, \"notification_sound\": true, \"push_notifications\": true, \"email_notifications\": true}}\";s:10:\"created_at\";s:19:\"2025-12-28 14:52:53\";s:10:\"updated_at\";s:19:\"2026-01-05 21:35:45\";}s:11:\"\0*\0original\";a:32:{s:2:\"id\";i:1;s:4:\"name\";s:16:\"The Willis Hotel\";s:4:\"logo\";s:20:\"images/logo/logo.svg\";s:9:\"logo_icon\";s:25:\"images/logo/logo-icon.svg\";s:9:\"logo_dark\";s:25:\"images/logo/logo-dark.svg\";s:7:\"favicon\";s:23:\"images/logo/favicon.ico\";s:6:\"slogan\";s:228:\"Conveniently located along Sotik - Narok Highway with easy access to both Maasai Mara Game Reserve and the culture-filled Kalenjin and Maasai communities, we offer the perfect choice hotel for both business and leisure traveler.\";s:7:\"socials\";s:490:\"[{\"icon\": \"fab fa-facebook-f\", \"link\": \"https://www.facebook.com/Willishotel/\", \"name\": \"facebook\"}, {\"icon\": \"fab fa-twitter\", \"link\": \"https://x.com/thewillishotel?lang=en\", \"name\": \"x\"}, {\"icon\": \"fab fa-instagram\", \"link\": \"https://www.instagram.com/the_willis_hotel/?hl=en\", \"name\": \"instagram\"}, {\"icon\": \"fab fa-tripadvisor\", \"link\": \"https://www.tripadvisor.com/Hotel_Review-g7701883-d23330748-Reviews-The_Willis_Hotel_Bomet-Bomet_Rift_Valley_Province.html\", \"name\": \"tripadvisor\"}]\";s:8:\"timezone\";s:14:\"Africa/Nairobi\";s:11:\"date_format\";s:5:\"d/m/Y\";s:11:\"time_format\";s:5:\"H:i:s\";s:8:\"currency\";s:3:\"KES\";s:8:\"about_us\";s:696:\"{\"extra\": \"So when it comes to booking the perfect hotel, vacation rental, resort, apartment, guest house, or tree house, we’ve got you covered.\", \"title\": \"About Us\", \"subtitle\": \"South Rift\'s Finest\", \"description\": \"Nearness to the lake region counties which includes Narok county, Kericho, Kisii, Migori, Homabay and Kisumu counties respectively. In addition, it’s a gate way to the neighbouring countries of Uganda and Tanzania. It’s also a gateway to the Mau forest and the Tea growing highlands of both Bomet and Kericho counties. It’s within the Maasai Mara tourist circuit and a link to the North Rift of Nakuru and Eldoret tourist sites and hospitality industry in the region.\"}\";s:15:\"currency_symbol\";s:3:\"KES\";s:13:\"primary_color\";s:7:\"#ff8c00\";s:15:\"secondary_color\";s:7:\"#000000\";s:13:\"contact_email\";s:24:\"hotelthewillis@gmail.com\";s:13:\"contact_phone\";s:12:\"0758 000 010\";s:7:\"address\";s:30:\"P.O Box 669-20400 Bomet, Kenya\";s:16:\"meta_description\";N;s:13:\"meta_keywords\";N;s:12:\"facebook_url\";s:37:\"https://www.facebook.com/Willishotel/\";s:11:\"twitter_url\";s:36:\"https://x.com/thewillishotel?lang=en\";s:13:\"instagram_url\";s:50:\"https://www.instagram.com/the_willis_hotel/?hl=en|\";s:12:\"linkedin_url\";N;s:16:\"maintenance_mode\";i:1;s:16:\"pagination_limit\";i:15;s:10:\"custom_css\";N;s:9:\"custom_js\";N;s:8:\"settings\";s:422:\"{\"backup\": {\"auto_backup\": true, \"backup_to_cloud\": false, \"backup_frequency\": \"daily\", \"backup_retention\": 30}, \"security\": {\"login_attempts\": \"5\", \"password_expiry\": 90, \"session_timeout\": \"30\", \"two_factor_auth\": false}, \"integrations\": {\"google_maps_key\": \"\", \"google_analytics\": null}, \"notifications\": {\"sms_notifications\": false, \"notification_sound\": true, \"push_notifications\": true, \"email_notifications\": true}}\";s:10:\"created_at\";s:19:\"2025-12-28 14:52:53\";s:10:\"updated_at\";s:19:\"2026-01-05 21:35:45\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:16:\"maintenance_mode\";s:7:\"boolean\";s:8:\"settings\";s:5:\"array\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:25:{i:0;s:4:\"name\";i:1;s:4:\"logo\";i:2;s:7:\"favicon\";i:3;s:6:\"slogan\";i:4;s:8:\"timezone\";i:5;s:11:\"date_format\";i:6;s:11:\"time_format\";i:7;s:8:\"currency\";i:8;s:15:\"currency_symbol\";i:9;s:13:\"primary_color\";i:10;s:15:\"secondary_color\";i:11;s:13:\"contact_email\";i:12;s:13:\"contact_phone\";i:13;s:7:\"address\";i:14;s:16:\"meta_description\";i:15;s:13:\"meta_keywords\";i:16;s:12:\"facebook_url\";i:17;s:11:\"twitter_url\";i:18;s:13:\"instagram_url\";i:19;s:12:\"linkedin_url\";i:20;s:16:\"maintenance_mode\";i:21;s:16:\"pagination_limit\";i:22;s:10:\"custom_css\";i:23;s:9:\"custom_js\";i:24;s:8:\"settings\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2083008945);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Reception','REC','2025-12-28 11:58:17','2025-12-28 11:58:17'),(2,'Housekeeping','HK','2025-12-28 11:58:17','2025-12-28 11:58:17'),(3,'Kitchen','KIT','2025-12-28 11:58:17','2025-12-28 11:58:17'),(4,'Procurement','PROC','2025-12-28 11:58:17','2025-12-28 11:58:17'),(5,'Human Resources','HR','2025-12-28 11:58:17','2025-12-28 11:58:17'),(6,'Management','MGT','2025-12-28 11:58:17','2025-12-28 11:58:17');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facilities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `capacity` int NOT NULL,
  `base_rate` decimal(12,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amenities` json DEFAULT NULL,
  `status` enum('available','unavailable','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facilities_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (1,'Lunch Only','lunch_only',50,1800.00,NULL,'[\"Mints\", \"Flip-charts\", \"P.A system\", \"Stationery\", \"Mineral water\"]','available','2025-12-30 07:32:42','2025-12-30 07:32:42'),(2,'Half Day (Lunch and 10 O\'Clock Tea)','half_day',50,2000.00,NULL,'[\"Mints\", \"Flip-charts\", \"P.A system\", \"Stationery\", \"Mineral water\"]','available','2025-12-30 07:32:42','2025-12-30 07:32:42'),(3,'Full Day (Lunch and 2 Teas)','full_day',50,2500.00,NULL,'[\"Mints\", \"Flip-charts\", \"P.A system\", \"Stationery\", \"Mineral water\"]','available','2025-12-30 07:32:42','2025-12-30 07:32:42'),(4,'Projector Hire per day','projector_hire',1,3000.00,NULL,'[\"Mints\", \"Flip-charts\", \"P.A system\", \"Stationery\", \"Mineral water\"]','available','2025-12-30 07:32:42','2025-12-30 07:32:42');
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facility_allocations`
--

DROP TABLE IF EXISTS `facility_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facility_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stay_id` bigint unsigned NOT NULL,
  `facility_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `rate_applied` decimal(12,2) NOT NULL,
  `status` enum('booked','confirmed','in_use','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'booked',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facility_allocations_stay_id_foreign` (`stay_id`),
  KEY `facility_allocations_package_id_foreign` (`package_id`),
  KEY `facility_allocations_facility_id_start_time_end_time_index` (`facility_id`,`start_time`,`end_time`),
  CONSTRAINT `facility_allocations_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`),
  CONSTRAINT `facility_allocations_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `facility_packages` (`id`),
  CONSTRAINT `facility_allocations_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facility_allocations`
--

LOCK TABLES `facility_allocations` WRITE;
/*!40000 ALTER TABLE `facility_allocations` DISABLE KEYS */;
/*!40000 ALTER TABLE `facility_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facility_packages`
--

DROP TABLE IF EXISTS `facility_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facility_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `facility_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `duration_hours` int NOT NULL,
  `inclusions` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `facility_packages_facility_id_foreign` (`facility_id`),
  CONSTRAINT `facility_packages_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facility_packages`
--

LOCK TABLES `facility_packages` WRITE;
/*!40000 ALTER TABLE `facility_packages` DISABLE KEYS */;
/*!40000 ALTER TABLE `facility_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guest_profiles`
--

DROP TABLE IF EXISTS `guest_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guest_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `id_type` enum('passport','national_id','driving_license','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `guest_profiles_id_type_id_number_unique` (`id_type`,`id_number`),
  KEY `guest_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `guest_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guest_profiles`
--

LOCK TABLES `guest_profiles` WRITE;
/*!40000 ALTER TABLE `guest_profiles` DISABLE KEYS */;
INSERT INTO `guest_profiles` VALUES (1,1,'passport','111111','Heard Island and McDonald Islands',NULL,NULL,NULL,NULL,NULL,'active'),(2,9,'national_id','31425580','Australia','50054','\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"kibettdennis@gmail.com\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[],\\\"entertainment\\\":[],\\\"room_service\\\":[],\\\"restaurant\\\":[],\\\"checkin_time\\\":[],\\\"other\\\":null,\\\"allergies\\\":null}\"','2025-12-30 08:11:28','2026-01-01 07:37:29','active'),(8,15,'passport','34458665','kenyan','50054',NULL,'[]','2025-12-30 08:14:41','2025-12-30 08:14:41','active'),(9,16,'passport','1234456','kENYAN','50054',NULL,'[]','2025-12-30 08:26:53','2025-12-30 08:26:53','active'),(10,17,'national_id','9995893','Kenyan','50054',NULL,'[]','2025-12-30 08:28:16','2025-12-30 08:28:16','active'),(11,18,'national_id','6546546','Uganda','50054',NULL,'[]','2025-12-30 08:36:37','2025-12-30 08:36:37','active'),(12,19,'passport','786875','India','50054',NULL,'[]','2025-12-30 08:37:20','2025-12-30 08:37:20','active'),(13,20,'national_id','56456','India','50054',NULL,'[]','2025-12-30 08:41:17','2025-12-30 08:41:17','active'),(14,21,'national_id','4654654','UG','50054',NULL,'[]','2025-12-30 08:45:05','2025-12-30 08:45:05','active'),(15,22,'driving_license','765456456','United States','50054',NULL,'[]','2025-12-30 08:51:07','2025-12-30 08:51:07','active'),(16,23,'driving_license','76576567','Kenyan','50054',NULL,'[]','2025-12-30 08:54:22','2025-12-30 08:54:22','active'),(17,24,'driving_license','jhgiugugi','ke','50054',NULL,'[]','2025-12-30 12:54:47','2025-12-30 12:54:47','active'),(18,25,'national_id','5434534','67467','50054',NULL,'[]','2025-12-30 13:46:08','2025-12-30 13:46:08','active'),(19,26,'other','564465567','Kenya','50054',NULL,'[]','2025-12-30 14:00:32','2025-12-30 14:00:32','active'),(20,27,'driving_license','ur6567858','Kenya','50054',NULL,'[]','2025-12-30 14:05:01','2025-12-30 14:05:01','active'),(21,28,'national_id','7657676','Heard Island and McDonald Islands','50054','\"{\\\"name\\\":\\\"Seeenno\\\",\\\"email\\\":\\\"karleighdeno@gmail.com\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"parent\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"high_floor\\\",\\\"pool_view\\\"],\\\"entertainment\\\":[\\\"cable_tv\\\",\\\"disney\\\"],\\\"room_service\\\":[\\\"limited\\\"],\\\"restaurant\\\":[\\\"window_table\\\"],\\\"checkin_time\\\":[\\\"standard_time\\\"],\\\"other\\\":\\\"54678\\\",\\\"allergies\\\":\\\"gfdhjkl;\'\\\"}\"','2025-12-30 16:53:52','2025-12-31 05:52:14','active'),(22,29,'driving_license','123456789874','Austria','50054',NULL,'\"[]\"','2025-12-30 16:57:03','2025-12-30 16:57:03','active'),(23,30,'other','764213','Albania','50054',NULL,'\"[]\"','2025-12-30 16:58:40','2025-12-30 16:58:40','active'),(24,31,'national_id','654654890','Akrotiri','50054',NULL,'\"[]\"','2025-12-30 17:00:54','2025-12-30 17:00:54','active'),(25,32,'other','6778777','Andorra','50054',NULL,'\"[]\"','2025-12-30 17:15:27','2025-12-30 17:15:27','active'),(27,34,'driving_license','76423467','Malawi','50054',NULL,'\"[]\"','2025-12-30 17:21:03','2025-12-30 17:21:03','active'),(28,35,'driving_license','555567890','Monaco','50054',NULL,'\"[]\"','2025-12-30 17:23:07','2025-12-30 17:23:07','active'),(30,37,'driving_license','89796574635','Kazakhstan','50054',NULL,'\"[]\"','2025-12-30 17:31:54','2025-12-30 17:31:54','active'),(32,39,'national_id','45675869765','Cook Islands','50054','\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"karleighdeno@gmail.com\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[],\\\"entertainment\\\":[],\\\"room_service\\\":[],\\\"restaurant\\\":[],\\\"checkin_time\\\":[],\\\"other\\\":null,\\\"allergies\\\":null}\"','2025-12-30 17:37:49','2025-12-31 05:40:15','active'),(34,41,'national_id','89678574635','Eritrea','50054','\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"karleighdeno@gmail.com\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[],\\\"entertainment\\\":[],\\\"room_service\\\":[],\\\"restaurant\\\":[],\\\"checkin_time\\\":[],\\\"other\\\":null,\\\"allergies\\\":null}\"','2025-12-30 17:39:11','2025-12-31 05:44:15','active'),(35,42,'driving_license','875646352','Sri Lanka','50054','{\"name\":\"Dennis Kibet Koech\",\"email\":\"info@imaginenation.co.ke\",\"phone\":\"0717492048\",\"relationship\":\"friend\",\"address\":\"50054\"}','\"[]\"','2025-12-30 17:41:35','2025-12-31 05:19:41','active'),(36,43,'other','7645678','United Kingdom','50054','\"{\\\"name\\\":\\\"Sam\\\",\\\"email\\\":\\\"samsontanui51@gmail.com\\\",\\\"phone\\\":\\\"0701607959\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"Kisumu\\\"}\"','\"{\\\"room_preference\\\":[\\\"smoking\\\"],\\\"entertainment\\\":[\\\"cable_tv\\\"],\\\"room_service\\\":[\\\"limited\\\"],\\\"restaurant\\\":[\\\"window_table\\\",\\\"private_booth\\\",\\\"outdoor\\\"],\\\"checkin_time\\\":[\\\"early_check_in\\\",\\\"flexible\\\"],\\\"other\\\":null,\\\"allergies\\\":null}\"','2025-12-30 18:18:31','2026-01-01 06:51:41','active'),(39,46,'other','75643245','Macau','50054','\"{\\\"name\\\":\\\"Dennis Kibet Koech\\\",\\\"email\\\":\\\"info@imaginenation.co.ke\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[],\\\"entertainment\\\":[],\\\"room_service\\\":[],\\\"restaurant\\\":[],\\\"checkin_time\\\":[],\\\"other\\\":\\\"676dr\\\",\\\"allergies\\\":\\\"milk in large quantities\\\"}\"','2025-12-31 03:57:27','2025-12-31 05:39:21','active'),(40,47,'national_id','43567','Bangladesh','50054','\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"info@imaginenation.co.ke\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"friend\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"quiet_room\\\",\\\"high_floor\\\"],\\\"entertainment\\\":[\\\"cable_tv\\\"],\\\"room_service\\\":[\\\"limited\\\"],\\\"restaurant\\\":[\\\"quiet_spot\\\"],\\\"checkin_time\\\":[\\\"standard_time\\\"],\\\"other\\\":\\\"Do you have a sauna?\\\",\\\"allergies\\\":\\\"Not too spicy\\\"}\"','2025-12-31 05:15:31','2026-01-01 09:57:36','active'),(41,48,'driving_license','767456','Aruba','50054','\"{\\\"name\\\":\\\"Saamson\\\",\\\"email\\\":\\\"info@imaginenation.co.ke\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"near_elevator\\\"],\\\"entertainment\\\":[\\\"netflix\\\",\\\"cable_tv\\\",\\\"hbo\\\"],\\\"room_service\\\":[\\\"evening\\\",\\\"morning\\\",\\\"limited\\\"],\\\"restaurant\\\":[\\\"window_table\\\",\\\"quiet_spot\\\"],\\\"checkin_time\\\":[\\\"flexible\\\"],\\\"other\\\":\\\"milk\\\",\\\"allergies\\\":\\\"milk\\\"}\"','2025-12-31 05:45:47','2026-01-01 06:53:13','active'),(42,49,'passport','87675463524','Algeria','50054','\"{\\\"name\\\":\\\"Dennis Kibet Koech\\\",\\\"email\\\":\\\"info@imaginenation.co.ke\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"near_elevator\\\"],\\\"entertainment\\\":[\\\"cable_tv\\\"],\\\"room_service\\\":[\\\"morning\\\",\\\"limited\\\"],\\\"restaurant\\\":[\\\"window_table\\\"],\\\"checkin_time\\\":[\\\"flexible\\\",\\\"late_check_in\\\"],\\\"other\\\":\\\"gfuyf uft\\\",\\\"allergies\\\":\\\"jik\\\"}\"','2025-12-31 05:49:12','2026-01-01 07:37:03','active'),(43,50,'driving_license','7865432','Albania','50054','\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"karleighdeno@gmail.com\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"friend\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"pool_view\\\",\\\"city_view\\\",\\\"smoking\\\",\\\"connecting\\\"],\\\"entertainment\\\":[\\\"cable_tv\\\",\\\"netflix\\\"],\\\"room_service\\\":[\\\"limited\\\"],\\\"restaurant\\\":[\\\"window_table\\\"],\\\"checkin_time\\\":[],\\\"other\\\":null,\\\"allergies\\\":null}\"','2025-12-31 06:28:56','2026-01-01 08:06:07','active'),(44,51,'national_id','8765345467','Bhutan','50054','\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"karleighdeno@gmail.com\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"spouse\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"near_elevator\\\"],\\\"entertainment\\\":[\\\"hbo\\\"],\\\"room_service\\\":[],\\\"restaurant\\\":[],\\\"checkin_time\\\":[\\\"standard_time\\\"],\\\"other\\\":null,\\\"allergies\\\":null}\"','2026-01-01 08:06:50','2026-01-01 08:06:50','active'),(45,52,'national_id','00008080','Norway','Kimolwet','\"{\\\"name\\\":\\\"Boots\\\",\\\"email\\\":\\\"boots@gmail.com\\\",\\\"phone\\\":\\\"071786736\\\",\\\"relationship\\\":\\\"sibling\\\",\\\"address\\\":\\\"Boots\\\"}\"','\"{\\\"room_preference\\\":[\\\"city_view\\\"],\\\"entertainment\\\":[\\\"netflix\\\",\\\"hbo\\\",\\\"disney\\\",\\\"gaming\\\"],\\\"room_service\\\":[\\\"full_service\\\"],\\\"restaurant\\\":[\\\"any\\\"],\\\"checkin_time\\\":[\\\"flexible\\\"],\\\"other\\\":\\\"Meaty menu and a lot of BBQ\\\",\\\"allergies\\\":\\\"Milk\\\"}\"','2026-01-01 12:01:30','2026-01-03 13:50:09','active'),(46,53,'national_id','7890987','Belgium',NULL,'\"{\\\"name\\\":\\\"Dennis Kibet\\\",\\\"email\\\":\\\"info@imaginenation.co.ke\\\",\\\"phone\\\":\\\"0717492048\\\",\\\"relationship\\\":\\\"parent\\\",\\\"address\\\":\\\"50054\\\"}\"','\"{\\\"room_preference\\\":[\\\"quiet_room\\\",\\\"connecting\\\"],\\\"entertainment\\\":[\\\"netflix\\\",\\\"music\\\",\\\"gaming\\\"],\\\"room_service\\\":[\\\"minibar\\\"],\\\"restaurant\\\":[\\\"quiet_spot\\\",\\\"outdoor\\\"],\\\"checkin_time\\\":[\\\"flexible\\\"],\\\"other\\\":\\\"Can I get a pack of Cigars?\\\",\\\"allergies\\\":\\\"No allergies\\\"}\"','2026-01-04 10:27:43','2026-01-04 10:27:43','active');
/*!40000 ALTER TABLE `guest_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_type` enum('cleaning','kitchen','office','maintenance','food','beverage','linen','amenity','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `category` enum('consumable','non_consumable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'consumable',
  `description` text COLLATE utf8mb4_unicode_ci,
  `unit_of_measure` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `reorder_level` decimal(10,2) NOT NULL DEFAULT '0.00',
  `unit_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `primary_supplier_id` bigint unsigned DEFAULT NULL,
  `minimum_stock` decimal(10,2) DEFAULT NULL,
  `maximum_stock` decimal(10,2) DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_items_sku_unique` (`sku`),
  KEY `inventory_items_primary_supplier_id_foreign` (`primary_supplier_id`),
  CONSTRAINT `inventory_items_primary_supplier_id_foreign` FOREIGN KEY (`primary_supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `source_type` enum('room','food','facility','service','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `unit_price` decimal(12,2) NOT NULL,
  `total` decimal(12,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) VIRTUAL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  KEY `invoice_items_source_type_reference_id_index` (`source_type`,`reference_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` (`id`, `invoice_id`, `source_type`, `reference_id`, `description`, `quantity`, `unit_price`, `created_at`, `updated_at`) VALUES (1,1,'room',NULL,'Room Accommodation - 2',1.00,7000.00,'2026-01-02 06:14:43','2026-01-02 06:14:43'),(2,2,'room',NULL,'Room Accommodation - 2',3.00,5000.00,'2026-01-02 16:54:42','2026-01-02 16:54:42'),(3,3,'room',NULL,'Room 2 - Deluxe rooms (Main accommodation)',1.00,5000.00,'2026-01-03 08:24:45','2026-01-03 08:24:45'),(4,3,'room',NULL,'Room 1 - Executive rooms (Main accommodation)',1.00,7000.00,'2026-01-03 08:24:45','2026-01-03 08:24:45'),(5,4,'room',NULL,'Room 2 - Deluxe rooms (Main accommodation)',1.00,5000.00,'2026-01-03 10:28:21','2026-01-03 10:28:21'),(6,5,'room',NULL,'Room 1 - Executive rooms (Main accommodation)',2.00,7000.00,'2026-01-04 10:32:03','2026-01-04 10:32:03');
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stay_id` bigint unsigned NOT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00',
  `tax_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `due_amount` decimal(12,2) GENERATED ALWAYS AS ((`total` - `paid_amount`)) VIRTUAL,
  `status` enum('draft','sent','partial','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_stay_id_foreign` (`stay_id`),
  KEY `invoices_status_due_date_index` (`status`,`due_date`),
  CONSTRAINT `invoices_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` (`id`, `stay_id`, `invoice_number`, `subtotal`, `tax_amount`, `discount_amount`, `total`, `paid_amount`, `status`, `issue_date`, `due_date`, `notes`, `created_at`, `updated_at`) VALUES (1,25,'INV-000001',7000.00,0.00,0.00,0.00,0.00,'draft','2026-01-02','2026-01-02','Initial booking invoice','2026-01-02 06:14:43','2026-01-02 06:14:43'),(2,27,'INV-000002',15000.00,0.00,0.00,0.00,0.00,'draft','2026-01-02','2026-01-04','Initial booking invoice','2026-01-02 16:54:42','2026-01-02 16:54:42'),(3,29,'INV-000003',0.00,0.00,0.00,0.00,0.00,'draft','2026-01-03','2026-11-04','Initial booking invoice','2026-01-03 08:24:45','2026-01-03 08:24:45'),(4,32,'INV-000004',0.00,0.00,0.00,0.00,0.00,'draft','2026-01-03','2026-01-06','Initial booking invoice','2026-01-03 10:28:21','2026-01-03 10:28:21'),(5,33,'INV-000005',0.00,0.00,0.00,0.00,0.00,'draft','2026-01-04','2026-01-04','Initial booking invoice','2026-01-04 10:32:03','2026-01-04 10:32:03');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_order_items`
--

DROP TABLE IF EXISTS `kitchen_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `menu_item_variant_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL,
  `status` enum('pending','preparing','ready','delivered') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kitchen_order_items_order_id_foreign` (`order_id`),
  KEY `kitchen_order_items_menu_item_variant_id_foreign` (`menu_item_variant_id`),
  CONSTRAINT `kitchen_order_items_menu_item_variant_id_foreign` FOREIGN KEY (`menu_item_variant_id`) REFERENCES `menu_item_variants` (`id`),
  CONSTRAINT `kitchen_order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `kitchen_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_order_items`
--

LOCK TABLES `kitchen_order_items` WRITE;
/*!40000 ALTER TABLE `kitchen_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `kitchen_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kitchen_orders`
--

DROP TABLE IF EXISTS `kitchen_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kitchen_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stay_id` bigint unsigned NOT NULL,
  `order_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','preparing','ready','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `type` enum('dine_in','room_service','takeaway') COLLATE utf8mb4_unicode_ci NOT NULL,
  `special_instructions` text COLLATE utf8mb4_unicode_ci,
  `placed_by` bigint unsigned NOT NULL,
  `preparation_started_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kitchen_orders_order_number_unique` (`order_number`),
  KEY `kitchen_orders_stay_id_foreign` (`stay_id`),
  KEY `kitchen_orders_placed_by_foreign` (`placed_by`),
  KEY `kitchen_orders_status_created_at_index` (`status`,`created_at`),
  CONSTRAINT `kitchen_orders_placed_by_foreign` FOREIGN KEY (`placed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `kitchen_orders_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kitchen_orders`
--

LOCK TABLES `kitchen_orders` WRITE;
/*!40000 ALTER TABLE `kitchen_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `kitchen_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `leave_type` enum('annual','sick','maternity','paternity','unpaid','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration_days` int GENERATED ALWAYS AS (((to_days(`end_date`) - to_days(`start_date`)) + 1)) VIRTUAL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_requests_approved_by_foreign` (`approved_by`),
  KEY `leave_requests_user_id_status_start_date_index` (`user_id`,`status`,`start_date`),
  CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_categories`
--

LOCK TABLES `menu_categories` WRITE;
/*!40000 ALTER TABLE `menu_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_variants`
--

DROP TABLE IF EXISTS `menu_item_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_item_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `preparation_time` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_item_variants_menu_item_id_foreign` (`menu_item_id`),
  CONSTRAINT `menu_item_variants_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_variants`
--

LOCK TABLES `menu_item_variants` WRITE;
/*!40000 ALTER TABLE `menu_item_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_item_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_category_id_foreign` (`category_id`),
  CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `menu_categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'0001_01_01_000003_create_system_table',1),(5,'0001_01_01_000004_create_profiles_tables',1),(6,'0001_01_01_000005_create_stays_and_rooms',1),(7,'0001_01_01_000006_create_facilities_tables',1),(8,'0001_01_01_000007_create_billing_tables',1),(9,'0001_01_01_000008_create_kitchen_tables',1),(10,'0001_01_01_000010_create_procurement_tables copy',1),(11,'0001_01_01_000011_create_hr_tables',1),(12,'0001_01_01_000012_create_audit_trails_table',1),(13,'0001_01_01_000012_create_tasks_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('kibettdennis@gmail.com','$2y$12$5fsgZB.PdBgfsFerMOG5rOOiw1ZY/4tRZeaVBmHiMtzVb9Mp220ze','2026-01-05 17:36:08');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('pending','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `method` enum('cash','credit_card','mobile_money','bank_transfer','cheque') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_details` json DEFAULT NULL,
  `received_by` bigint unsigned NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_reference_unique` (`payment_reference`),
  KEY `payments_received_by_foreign` (`received_by`),
  KEY `payments_invoice_id_received_at_index` (`invoice_id`,`received_at`),
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payrolls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `period_month` date NOT NULL,
  `basic_salary` decimal(12,2) NOT NULL,
  `overtime_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `allowances` decimal(12,2) NOT NULL DEFAULT '0.00',
  `bonuses` decimal(12,2) NOT NULL DEFAULT '0.00',
  `gross` decimal(12,2) GENERATED ALWAYS AS ((((`basic_salary` + `overtime_amount`) + `allowances`) + `bonuses`)) VIRTUAL,
  `tax_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `other_deductions` decimal(12,2) NOT NULL DEFAULT '0.00',
  `net` decimal(12,2) GENERATED ALWAYS AS (((`gross` - `tax_deductions`) - `other_deductions`)) VIRTUAL,
  `status` enum('draft','calculated','approved','paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payrolls_user_id_period_month_unique` (`user_id`,`period_month`),
  KEY `payrolls_approved_by_foreign` (`approved_by`),
  KEY `payrolls_period_month_status_index` (`period_month`,`status`),
  CONSTRAINT `payrolls_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `payrolls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint unsigned NOT NULL,
  `inventory_item_id` bigint unsigned NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(12,2) GENERATED ALWAYS AS ((`quantity` * `unit_price`)) VIRTUAL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_items_purchase_order_id_inventory_item_id_unique` (`purchase_order_id`,`inventory_item_id`),
  KEY `purchase_order_items_inventory_item_id_foreign` (`inventory_item_id`),
  CONSTRAINT `purchase_order_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint unsigned NOT NULL,
  `po_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `status` enum('draft','submitted','approved','ordered','received','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `delivery_date` date DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `ordered_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_orders_po_number_unique` (`po_number`),
  KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  KEY `purchase_orders_requested_by_foreign` (`requested_by`),
  KEY `purchase_orders_approved_by_foreign` (`approved_by`),
  CONSTRAINT `purchase_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  CONSTRAINT `purchase_orders_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`),
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (2,1),(3,1),(9,7),(15,7),(16,7),(17,7),(18,7),(19,7),(20,7),(21,7),(22,7),(23,7),(24,7),(25,7),(26,7),(27,7),(28,7),(29,7),(30,7),(31,7),(32,7),(34,7),(35,7),(37,7),(39,7),(41,7),(42,7),(43,7),(44,7),(46,7),(47,7),(48,7),(49,7),(50,7),(51,7),(52,7),(53,7);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','System Administrator','2025-12-28 11:58:17','2025-12-28 11:58:17'),(2,'receptionist','Front Desk Staff','2025-12-28 11:58:17','2025-12-28 11:58:17'),(3,'housekeeping','Cleaning Staff','2025-12-28 11:58:17','2025-12-28 11:58:17'),(4,'kitchen','Kitchen Staff','2025-12-28 11:58:17','2025-12-28 11:58:17'),(5,'procurement','Procurement Officer','2025-12-28 11:58:17','2025-12-28 11:58:17'),(6,'hr','Human Resources','2025-12-28 11:58:17','2025-12-28 11:58:17'),(7,'guest','Hotel Guest','2025-12-28 11:58:17','2025-12-28 11:58:17'),(8,'manager','Hotel Manager','2025-12-28 11:58:17','2025-12-28 11:58:17'),(9,'finance','Finance Manager',NULL,NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_allocations`
--

DROP TABLE IF EXISTS `room_allocations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stay_id` bigint unsigned NOT NULL,
  `room_id` bigint unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `rate_applied` decimal(12,2) NOT NULL,
  `adults` int NOT NULL DEFAULT '1',
  `children` int NOT NULL DEFAULT '0',
  `guest_notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_allocations_stay_id_room_id_from_date_unique` (`stay_id`,`room_id`,`from_date`),
  KEY `room_allocations_room_id_from_date_to_date_index` (`room_id`,`from_date`,`to_date`),
  CONSTRAINT `room_allocations_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `room_allocations_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_allocations`
--

LOCK TABLES `room_allocations` WRITE;
/*!40000 ALTER TABLE `room_allocations` DISABLE KEYS */;
INSERT INTO `room_allocations` VALUES (5,1,2,'2025-12-30','2026-01-02',7000.00,1,0,NULL,NULL,NULL),(6,11,2,'2026-02-02','2026-02-05',7000.00,1,0,NULL,'2026-01-02 05:30:16','2026-01-02 05:30:16'),(7,12,2,'2026-05-14','2026-05-21',7000.00,1,0,NULL,'2026-01-02 05:32:19','2026-01-02 05:32:19'),(8,13,2,'2026-07-03','2026-08-01',7000.00,1,0,NULL,'2026-01-02 05:34:16','2026-01-02 05:34:16'),(9,15,2,'2026-02-07','2026-02-09',7000.00,1,0,NULL,'2026-01-02 05:44:48','2026-01-02 05:44:48'),(10,16,2,'2026-03-02','2026-03-03',7000.00,1,0,NULL,'2026-01-02 05:45:57','2026-01-02 05:45:57'),(11,21,2,'2026-08-02','2026-08-03',7000.00,1,0,NULL,'2026-01-02 05:50:51','2026-01-02 05:50:51'),(12,24,2,'2026-11-10','2026-11-11',7000.00,1,0,NULL,'2026-01-02 05:55:11','2026-01-02 05:55:11'),(13,25,3,'2026-01-02','2026-01-03',7000.00,1,0,NULL,'2026-01-02 06:14:43','2026-01-02 06:14:43'),(14,27,3,'2026-01-04','2026-01-05',5000.00,1,0,NULL,'2026-01-02 16:54:42','2026-01-02 16:54:42'),(15,29,3,'2026-11-04','2026-11-05',5000.00,1,0,NULL,'2026-01-03 08:24:45','2026-01-03 08:24:45'),(16,29,2,'2026-11-04','2026-11-05',7000.00,1,0,NULL,'2026-01-03 08:24:45','2026-01-03 08:24:45'),(17,32,3,'2026-01-06','2026-01-07',5000.00,1,0,NULL,'2026-01-03 10:28:21','2026-01-03 10:28:21'),(18,33,2,'2026-01-04','2026-01-06',7000.00,1,0,NULL,'2026-01-04 10:32:03','2026-01-04 10:32:03');
/*!40000 ALTER TABLE `room_allocations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_type_services`
--

DROP TABLE IF EXISTS `room_type_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_type_services` (
  `room_type_id` bigint unsigned NOT NULL,
  `service_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`room_type_id`,`service_name`),
  CONSTRAINT `room_type_services_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_type_services`
--

LOCK TABLES `room_type_services` WRITE;
/*!40000 ALTER TABLE `room_type_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_type_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_types`
--

DROP TABLE IF EXISTS `room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_rate` decimal(12,2) NOT NULL,
  `capacity` int NOT NULL,
  `bed_type` enum('single','double','queen','king','twin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amenities` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_types_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_types`
--

LOCK TABLES `room_types` WRITE;
/*!40000 ALTER TABLE `room_types` DISABLE KEYS */;
INSERT INTO `room_types` VALUES (1,'Executive Room','executive',7000.00,2,'king',NULL,NULL,'2025-12-30 07:32:42','2025-12-30 07:32:42'),(2,'Deluxe Room','deluxe',5000.00,2,'queen',NULL,NULL,'2025-12-30 07:32:42','2025-12-30 07:32:42'),(3,'Standard Room','standard',4000.00,2,'double',NULL,NULL,'2025-12-30 07:32:42','2025-12-30 07:32:42');
/*!40000 ALTER TABLE `room_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_type_id` bigint unsigned NOT NULL,
  `status` enum('available','occupied','cleaning','maintenance','out_of_order','reserved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `floor` int NOT NULL,
  `wing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `features` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `adult_price` decimal(12,2) DEFAULT '0.00',
  `child_price` decimal(12,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_room_number_unique` (`room_number`),
  KEY `rooms_room_type_id_foreign` (`room_type_id`),
  KEY `rooms_status_room_type_id_index` (`status`,`room_type_id`),
  CONSTRAINT `rooms_room_type_id_foreign` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (2,'1',1,'available',0,NULL,NULL,NULL,'2026-01-02 05:55:11',0.00,0.00),(3,'2',2,'occupied',0,NULL,NULL,NULL,'2026-01-03 12:00:45',0.00,0.00);
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `description` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('HnP0ewJI3rGkNILhDTZxGxGA1teaqPiE9IW9q76E',2,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTo1OntzOjY6Il90b2tlbiI7czo0MDoiU2I2bDJqZXBuQlZkOG1uVTRXYlBSVzhnN3NUQmdVU1UwYWJ2TlpqSiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvc3lzdGVtIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Mjt9',1767729824);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_assignments`
--

DROP TABLE IF EXISTS `shift_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shift_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `shift_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `status` enum('scheduled','completed','absent','on_leave') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shift_assignments_user_id_shift_id_date_unique` (`user_id`,`shift_id`,`date`),
  KEY `shift_assignments_shift_id_foreign` (`shift_id`),
  KEY `shift_assignments_date_status_index` (`date`,`status`),
  CONSTRAINT `shift_assignments_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`),
  CONSTRAINT `shift_assignments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_assignments`
--

LOCK TABLES `shift_assignments` WRITE;
/*!40000 ALTER TABLE `shift_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `shift_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `type` enum('morning','evening','night','custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff_profiles`
--

DROP TABLE IF EXISTS `staff_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `hire_date` date NOT NULL,
  `contract_period` int unsigned NOT NULL DEFAULT '3',
  `employment_status` enum('internship','probation','permanent','contract','terminated') COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_profiles_user_id_foreign` (`user_id`),
  KEY `staff_profiles_department_id_foreign` (`department_id`),
  CONSTRAINT `staff_profiles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `staff_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff_profiles`
--

LOCK TABLES `staff_profiles` WRITE;
/*!40000 ALTER TABLE `staff_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `staff_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stay_service`
--

DROP TABLE IF EXISTS `stay_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stay_service` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `stay_id` bigint unsigned NOT NULL,
  `service_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_stay_service_stay` (`stay_id`),
  KEY `fk_stay_service_service` (`service_id`),
  CONSTRAINT `fk_stay_service_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_stay_service_stay` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stay_service`
--

LOCK TABLES `stay_service` WRITE;
/*!40000 ALTER TABLE `stay_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `stay_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stays`
--

DROP TABLE IF EXISTS `stays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `guest_id` bigint unsigned NOT NULL,
  `status` enum('booked','checked_in','checked_out','cancelled','reserved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved',
  `arrival_date` date NOT NULL,
  `departure_date` date NOT NULL,
  `adults` int NOT NULL DEFAULT '1',
  `children` int NOT NULL DEFAULT '0',
  `special_requests` text COLLATE utf8mb4_unicode_ci,
  `check_in` timestamp NULL DEFAULT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stays_guest_id_foreign` (`guest_id`),
  KEY `stays_created_by_foreign` (`created_by`),
  KEY `stays_status_arrival_date_index` (`status`,`arrival_date`),
  CONSTRAINT `stays_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `stays_guest_id_foreign` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stays`
--

LOCK TABLES `stays` WRITE;
/*!40000 ALTER TABLE `stays` DISABLE KEYS */;
INSERT INTO `stays` VALUES (1,1,'booked','2025-12-30','2026-01-02',1,0,NULL,'2025-12-29 21:00:00','2026-01-29 21:00:00',2,NULL,NULL,NULL),(7,1,'booked','2026-01-02','2026-01-12',4,0,'Get us the best views',NULL,NULL,2,'2026-01-02 05:15:41','2026-01-02 05:15:41',NULL),(8,1,'checked_out','2026-01-02','2026-01-07',6,0,NULL,NULL,'2026-01-02 16:13:39',2,'2026-01-02 05:17:07','2026-01-02 16:13:39',NULL),(9,1,'checked_out','2026-01-02','2026-01-03',1,0,NULL,NULL,'2026-01-02 16:20:33',2,'2026-01-02 05:18:05','2026-01-02 16:20:33',NULL),(10,52,'booked','2026-01-02','2026-01-07',4,0,NULL,NULL,NULL,2,'2026-01-02 05:29:57','2026-01-02 05:29:57',NULL),(11,52,'booked','2026-02-02','2026-02-05',4,0,NULL,NULL,NULL,2,'2026-01-02 05:30:16','2026-01-02 05:30:16',NULL),(12,1,'checked_in','2026-05-14','2026-05-21',5,0,NULL,NULL,NULL,2,'2026-01-02 05:32:19','2026-01-02 06:05:42',NULL),(13,52,'booked','2026-07-03','2026-08-01',1,0,NULL,NULL,NULL,2,'2026-01-02 05:34:16','2026-01-02 05:34:16',NULL),(14,52,'booked','2026-02-02','2026-02-09',1,0,'I am coming for the weekend',NULL,NULL,2,'2026-01-02 05:44:35','2026-01-02 05:44:35',NULL),(15,52,'booked','2026-02-07','2026-02-09',1,0,'I am coming for the weekend',NULL,NULL,2,'2026-01-02 05:44:48','2026-01-02 05:44:48',NULL),(16,52,'checked_in','2026-03-02','2026-03-03',1,0,NULL,NULL,NULL,2,'2026-01-02 05:45:57','2026-01-02 16:45:49',NULL),(17,49,'booked','2026-01-30','2026-02-02',2,0,NULL,NULL,NULL,2,'2026-01-02 05:49:58','2026-01-02 05:49:58',NULL),(18,49,'booked','2026-03-02','2026-03-10',1,0,NULL,NULL,NULL,2,'2026-01-02 05:50:21','2026-01-02 05:50:21',NULL),(19,49,'booked','2026-03-02','2026-03-08',1,0,NULL,NULL,NULL,2,'2026-01-02 05:50:28','2026-01-02 05:50:28',NULL),(20,49,'booked','2026-02-02','2026-02-03',1,0,NULL,NULL,NULL,2,'2026-01-02 05:50:44','2026-01-02 05:50:44',NULL),(21,49,'booked','2026-08-02','2026-08-03',1,0,NULL,NULL,NULL,2,'2026-01-02 05:50:51','2026-01-02 05:50:51',NULL),(22,49,'booked','2026-01-03','2026-01-04',1,0,NULL,NULL,NULL,2,'2026-01-02 05:54:53','2026-01-02 05:54:53',NULL),(23,49,'checked_in','2026-01-10','2026-01-11',1,0,NULL,NULL,NULL,2,'2026-01-02 05:55:02','2026-01-02 17:11:00',NULL),(24,49,'checked_in','2026-11-10','2026-11-11',1,0,NULL,NULL,NULL,2,'2026-01-02 05:55:11','2026-01-02 17:07:21',NULL),(25,51,'checked_in','2026-01-02','2026-01-03',1,0,NULL,NULL,NULL,2,'2026-01-02 06:14:43','2026-01-03 06:37:33',NULL),(26,48,'reserved','2026-01-03','2026-01-04',3,0,NULL,NULL,NULL,2,'2026-01-02 16:54:33','2026-01-03 13:48:50',NULL),(27,48,'checked_in','2026-01-04','2026-01-05',3,0,NULL,NULL,NULL,2,'2026-01-02 16:54:42','2026-01-03 07:42:52',NULL),(29,50,'booked','2026-11-04','2026-11-05',2,0,NULL,NULL,NULL,2,'2026-01-03 08:24:45','2026-01-03 08:24:45',NULL),(32,52,'checked_in','2026-01-06','2026-01-07',1,0,NULL,'2026-01-03 12:00:45',NULL,2,'2026-01-03 10:28:21','2026-01-03 12:00:45',NULL),(33,53,'booked','2026-01-06','2026-01-07',1,0,'My wife has an issue with hotel soaps, can we know what brand before hand?',NULL,NULL,2,'2026-01-04 10:32:03','2026-01-06 04:22:08',NULL);
/*!40000 ALTER TABLE `stays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_prices`
--

DROP TABLE IF EXISTS `supplier_prices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier_prices` (
  `supplier_id` bigint unsigned NOT NULL,
  `inventory_item_id` bigint unsigned NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`supplier_id`,`inventory_item_id`,`effective_from`),
  KEY `supplier_prices_inventory_item_id_foreign` (`inventory_item_id`),
  CONSTRAINT `supplier_prices_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `supplier_prices_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_prices`
--

LOCK TABLES `supplier_prices` WRITE;
/*!40000 ALTER TABLE `supplier_prices` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_prices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System/Company name',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System logo file path',
  `logo_icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_dark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Favicon file path',
  `slogan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System slogan or tagline',
  `socials` json DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC' COMMENT 'System default timezone',
  `date_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'd-m-Y' COMMENT 'Default date format',
  `time_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'H:i:s' COMMENT 'Default time format',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES' COMMENT 'Default currency',
  `about_us` json DEFAULT NULL,
  `currency_symbol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '$' COMMENT 'Currency symbol',
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Primary color for UI',
  `secondary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Secondary color for UI',
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System contact email',
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System contact phone',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'System physical address',
  `meta_description` text COLLATE utf8mb4_unicode_ci COMMENT 'SEO meta description',
  `meta_keywords` text COLLATE utf8mb4_unicode_ci COMMENT 'SEO meta keywords',
  `facebook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'System maintenance status',
  `pagination_limit` int NOT NULL DEFAULT '15' COMMENT 'Default items per page',
  `custom_css` text COLLATE utf8mb4_unicode_ci COMMENT 'Custom CSS styles',
  `custom_js` text COLLATE utf8mb4_unicode_ci COMMENT 'Custom JavaScript',
  `settings` json DEFAULT NULL COMMENT 'Additional settings in JSON format',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (1,'The Willis Hotel','images/logo/logo.svg','images/logo/logo-icon.svg','images/logo/logo-dark.svg','images/logo/favicon.ico','Conveniently located along Sotik - Narok Highway with easy access to both Maasai Mara Game Reserve and the culture-filled Kalenjin and Maasai communities, we offer the perfect choice hotel for both business and leisure traveler.','[{\"icon\": \"fab fa-facebook-f\", \"link\": \"https://www.facebook.com/Willishotel/\", \"name\": \"facebook\"}, {\"icon\": \"fab fa-twitter\", \"link\": \"https://x.com/thewillishotel?lang=en\", \"name\": \"x\"}, {\"icon\": \"fab fa-instagram\", \"link\": \"https://www.instagram.com/the_willis_hotel/?hl=en\", \"name\": \"instagram\"}, {\"icon\": \"fab fa-tripadvisor\", \"link\": \"https://www.tripadvisor.com/Hotel_Review-g7701883-d23330748-Reviews-The_Willis_Hotel_Bomet-Bomet_Rift_Valley_Province.html\", \"name\": \"tripadvisor\"}]','Africa/Nairobi','d/m/Y','H:i:s','KES','{\"extra\": \"So when it comes to booking the perfect hotel, vacation rental, resort, apartment, guest house, or tree house, we’ve got you covered.\", \"title\": \"About Us\", \"subtitle\": \"South Rift\'s Finest\", \"description\": \"Nearness to the lake region counties which includes Narok county, Kericho, Kisii, Migori, Homabay and Kisumu counties respectively. In addition, it’s a gate way to the neighbouring countries of Uganda and Tanzania. It’s also a gateway to the Mau forest and the Tea growing highlands of both Bomet and Kericho counties. It’s within the Maasai Mara tourist circuit and a link to the North Rift of Nakuru and Eldoret tourist sites and hospitality industry in the region.\"}','KES','#ff8c00','#000000','hotelthewillis@gmail.com','0758 000 010','P.O Box 669-20400 Bomet, Kenya',NULL,NULL,'https://www.facebook.com/Willishotel/','https://x.com/thewillishotel?lang=en','https://www.instagram.com/the_willis_hotel/?hl=en|',NULL,1,15,NULL,NULL,'{\"backup\": {\"auto_backup\": true, \"backup_to_cloud\": false, \"backup_frequency\": \"daily\", \"backup_retention\": 30}, \"security\": {\"login_attempts\": \"5\", \"password_expiry\": 90, \"session_timeout\": \"30\", \"two_factor_auth\": false}, \"integrations\": {\"google_maps_key\": \"\", \"google_analytics\": null}, \"notifications\": {\"sms_notifications\": false, \"notification_sound\": true, \"push_notifications\": true, \"email_notifications\": true}}','2025-12-28 11:52:53','2026-01-05 18:35:45');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_attachments`
--

DROP TABLE IF EXISTS `task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_attachments_task_id_foreign` (`task_id`),
  KEY `task_attachments_user_id_foreign` (`user_id`),
  CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_attachments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_attachments`
--

LOCK TABLES `task_attachments` WRITE;
/*!40000 ALTER TABLE `task_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_comments`
--

DROP TABLE IF EXISTS `task_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_comments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_comments_user_id_foreign` (`user_id`),
  KEY `task_comments_task_id_created_at_index` (`task_id`,`created_at`),
  CONSTRAINT `task_comments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `task_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_comments`
--

LOCK TABLES `task_comments` WRITE;
/*!40000 ALTER TABLE `task_comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_items`
--

DROP TABLE IF EXISTS `task_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint unsigned NOT NULL,
  `inventory_item_id` bigint unsigned NOT NULL,
  `quantity_used` decimal(10,2) NOT NULL,
  `unit_cost_at_time` decimal(10,2) NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_items_task_id_inventory_item_id_unique` (`task_id`,`inventory_item_id`),
  KEY `task_items_inventory_item_id_created_at_index` (`inventory_item_id`,`created_at`),
  CONSTRAINT `task_items_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`),
  CONSTRAINT `task_items_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_items`
--

LOCK TABLES `task_items` WRITE;
/*!40000 ALTER TABLE `task_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_types`
--

DROP TABLE IF EXISTS `task_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique code for task type',
  `department_id` bigint unsigned DEFAULT NULL,
  `category` enum('cleaning','maintenance','kitchen','reception','admin','hr','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cleaning',
  `description` text COLLATE utf8mb4_unicode_ci,
  `default_checklist` json DEFAULT NULL COMMENT 'JSON array of default checklist items',
  `default_estimated_minutes` int DEFAULT NULL,
  `default_estimated_cost` decimal(10,2) DEFAULT NULL,
  `requires_room` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether this task type requires a room assignment',
  `requires_inventory` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Whether this task type requires inventory items',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_types_code_unique` (`code`),
  KEY `task_types_department_id_foreign` (`department_id`),
  KEY `task_types_category_is_active_index` (`category`,`is_active`),
  CONSTRAINT `task_types_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_types`
--

LOCK TABLES `task_types` WRITE;
/*!40000 ALTER TABLE `task_types` DISABLE KEYS */;
INSERT INTO `task_types` VALUES (1,'Check-in Cleaning','CHECKIN_CLEAN',NULL,'cleaning','Thorough cleaning before guest check-in','[{\"item\": \"Change bed linens\", \"completed\": false}, {\"item\": \"Clean bathroom\", \"completed\": false}, {\"item\": \"Vacuum/mop floor\", \"completed\": false}, {\"item\": \"Dust surfaces\", \"completed\": false}, {\"item\": \"Restock amenities\", \"completed\": false}]',45,500.00,1,1,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL),(2,'Daily Cleaning','DAILY_CLEAN',NULL,'cleaning','Daily room cleaning for occupied rooms','[{\"item\": \"Make bed\", \"completed\": false}, {\"item\": \"Empty trash\", \"completed\": false}, {\"item\": \"Clean bathroom\", \"completed\": false}, {\"item\": \"Restock towels\", \"completed\": false}, {\"item\": \"Vacuum floor\", \"completed\": false}]',20,200.00,1,1,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL),(3,'Check-out Cleaning','CHECKOUT_CLEAN',NULL,'cleaning','Deep cleaning after guest check-out','[{\"item\": \"Strip bed linens\", \"completed\": false}, {\"item\": \"Deep clean bathroom\", \"completed\": false}, {\"item\": \"Clean all surfaces\", \"completed\": false}, {\"item\": \"Vacuum and mop floor\", \"completed\": false}, {\"item\": \"Check amenities\", \"completed\": false}]',60,800.00,1,1,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL),(4,'Deep Cleaning','DEEP_CLEAN',NULL,'cleaning','Weekly/Monthly deep cleaning','[{\"item\": \"Clean windows\", \"completed\": false}, {\"item\": \"Clean curtains\", \"completed\": false}, {\"item\": \"Clean under furniture\", \"completed\": false}, {\"item\": \"Clean vents\", \"completed\": false}, {\"item\": \"Deep clean bathroom\", \"completed\": false}]',120,1500.00,1,1,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL),(5,'Kitchen Cleaning','KITCHEN_CLEAN',NULL,'kitchen','Daily kitchen cleaning','[{\"item\": \"Clean countertops\", \"completed\": false}, {\"item\": \"Clean appliances\", \"completed\": false}, {\"item\": \"Mop floor\", \"completed\": false}, {\"item\": \"Take out trash\", \"completed\": false}, {\"item\": \"Restock supplies\", \"completed\": false}]',60,300.00,0,1,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL),(6,'Room Maintenance','ROOM_MAINT',NULL,'maintenance','General room maintenance','[{\"item\": \"Check plumbing\", \"completed\": false}, {\"item\": \"Check electrical\", \"completed\": false}, {\"item\": \"Check furniture\", \"completed\": false}, {\"item\": \"Check appliances\", \"completed\": false}]',30,0.00,1,0,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL),(7,'Guest Check-in','GUEST_CHECKIN',NULL,'reception','Process guest check-in','[{\"item\": \"Verify ID\", \"completed\": false}, {\"item\": \"Process payment\", \"completed\": false}, {\"item\": \"Assign room\", \"completed\": false}, {\"item\": \"Issue key\", \"completed\": false}, {\"item\": \"Explain amenities\", \"completed\": false}]',10,0.00,0,0,1,'2025-12-28 11:53:04','2025-12-28 11:53:04',NULL);
/*!40000 ALTER TABLE `task_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_type_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `room_id` bigint unsigned DEFAULT NULL,
  `stay_id` bigint unsigned DEFAULT NULL,
  `facility_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','assigned','in_progress','completed','verified','cancelled','on_hold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `estimated_minutes` int DEFAULT NULL,
  `actual_minutes` int DEFAULT NULL,
  `due_date` datetime NOT NULL,
  `scheduled_start` datetime DEFAULT NULL,
  `scheduled_end` datetime DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `checklist` json DEFAULT NULL COMMENT 'JSON array of checklist items with completion status',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurrence_pattern` enum('daily','weekly','monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recurrence_interval` int DEFAULT NULL,
  `recurrence_end_date` date DEFAULT NULL,
  `parent_task_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_shift_id_foreign` (`shift_id`),
  KEY `tasks_stay_id_foreign` (`stay_id`),
  KEY `tasks_facility_id_foreign` (`facility_id`),
  KEY `tasks_created_by_foreign` (`created_by`),
  KEY `tasks_verified_by_foreign` (`verified_by`),
  KEY `tasks_parent_task_id_foreign` (`parent_task_id`),
  KEY `tasks_task_type_id_status_index` (`task_type_id`,`status`),
  KEY `tasks_assigned_to_status_index` (`assigned_to`,`status`),
  KEY `tasks_room_id_status_index` (`room_id`,`status`),
  KEY `tasks_due_date_status_index` (`due_date`,`status`),
  KEY `tasks_status_priority_index` (`status`,`priority`),
  KEY `tasks_created_at_status_index` (`created_at`,`status`),
  KEY `tasks_department_id_status_index` (`department_id`,`status`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  CONSTRAINT `tasks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `tasks_facility_id_foreign` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`),
  CONSTRAINT `tasks_parent_task_id_foreign` FOREIGN KEY (`parent_task_id`) REFERENCES `tasks` (`id`),
  CONSTRAINT `tasks_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `tasks_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`),
  CONSTRAINT `tasks_stay_id_foreign` FOREIGN KEY (`stay_id`) REFERENCES `stays` (`id`),
  CONSTRAINT `tasks_task_type_id_foreign` FOREIGN KEY (`task_type_id`) REFERENCES `task_types` (`id`),
  CONSTRAINT `tasks_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,1,'Check-in Cleaning','Cleaning after guest check-in',NULL,NULL,NULL,3,25,NULL,'pending','high',NULL,NULL,'2026-01-03 11:37:33',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'2026-01-03 06:37:33','2026-01-03 06:37:33',NULL),(2,1,'Check-in Cleaning - Room 2','Cleaning after guest check-in',NULL,NULL,NULL,3,27,NULL,'pending','high',NULL,NULL,'2026-01-03 12:42:52',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'2026-01-03 07:42:52','2026-01-03 07:42:52',NULL),(3,1,'Check-in Cleaning - Room 2','Cleaning after guest check-in',NULL,NULL,NULL,3,32,NULL,'pending','high',NULL,NULL,'2026-01-03 17:00:45',NULL,NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'2026-01-03 12:00:45','2026-01-03 12:00:45',NULL);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dob` date DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User profile image',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social` json DEFAULT NULL COMMENT 'JSON field for social links',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com',NULL,'2025-12-28 11:52:55','$2y$12$Eygfov3KvB4SR2b9dmwhsudwpLy9bPRphINBgwbaLV.DZZFMiT996',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active','pMjCMP3Ofi','2025-12-28 11:52:55','2025-12-28 11:52:55'),(2,'Dennis Kibet','kibettdennis@gmail.com',NULL,NULL,'$2y$12$WusKZblm65R7BXzssX5vNuWKm5uDbJjZqy1SwUGgjDIhgYWMjySKW',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active','dUVGpAlDDxg5BxM41rIwmzAuW5DNKgXOqAoWThRwM5p6tW12Cdolt1Rya3X4','2025-12-28 11:55:11','2025-12-28 11:55:11'),(3,'System Admin','admin@hotel.com',NULL,NULL,'$2y$12$yEQv1uVodtCMj4iS67MQ1.drOXJ9zmEyHwBu.4eVFFW58VYilbk/m',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-28 11:58:17','2025-12-28 11:58:17'),(9,'Dennis Kibet','urbancreationslive@gmail.com',NULL,NULL,'$2y$12$trr6jg7C.SpTmPZGlKVNiOra3Y9wdKA8cxGtqjSS916CYnv5ur1xG',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:11:28','2025-12-30 08:11:28'),(15,'samson Koech','info@imaginenation.co.ke',NULL,NULL,'$2y$12$/D3pMXtqKoW8z3olQQt3K.SYPoIds84JvXZiwECHNBUqVaMfj9Dji',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:14:41','2025-12-30 08:14:41'),(16,'Joseph Koech','great@imaginenation.co.ke',NULL,NULL,'$2y$12$QI9q2rmQ2iKShauPB4Npa.w6LjPoaauU4GMLOCkdKD1Vs79HyOba2',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:26:53','2025-12-30 08:26:53'),(17,'LINNER KOECH','ljkoech@gmail.com',NULL,NULL,'$2y$12$ZashbdlJZnYq.82/oHGtmudFR4ik35ByRL9xfkBFz60ypEwi9I1o2',NULL,'0720540112',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:28:16','2025-12-30 08:28:16'),(18,'Dennis Koech','info00@imaginenation.co.ke',NULL,NULL,'$2y$12$d/z77II1EKEXfD40R7jtZ.ROKmNeKNmdCaqPhdQwsTT/ZwIzHhXei',NULL,'0717492047',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:36:37','2025-12-30 08:36:37'),(19,'uyteiu iuyiuyi','oyoi@hjgjh.com',NULL,NULL,'$2y$12$1UqA0zPvWeUmdo4nXpiiZOA5Bzj0HDOgN7Rlirp02dwynlP67ZAQK',NULL,'0717492044',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:37:20','2025-12-30 08:37:20'),(20,'gfsdfsdfg fffgh nch jbmnb','fhgfgh@jhghjg.com',NULL,NULL,'$2y$12$0BfwB8SH7eqx0u.FwRkAv..GShwwxWWd4q7/LCqLpV6vtXqUEczWS',NULL,'07174665048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:41:17','2025-12-30 08:41:17'),(21,'yrtyruy yu ruy yuyty yit yt','gyfyuj@gf.com',NULL,NULL,'$2y$12$dwc383DAwH0r1ejkocxLIuRW7iMzDbtjzT0KFE4k3qgFrcOHF8VEe',NULL,'07777492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:45:05','2025-12-30 08:45:05'),(22,'7676fgdgfdgf gytryt','karleighdeno@gmail.com',NULL,NULL,'$2y$12$QEkGhrhe6yDK90EUMv/czuLZ5Ow5xUja/sSEWUbA/cSEHgVhyP7q6',NULL,'0717492000',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:51:07','2025-12-30 08:51:07'),(23,'uiyiuyui kuiuyui','jhgigi@yt.com',NULL,NULL,'$2y$12$ddy0UaS/ZHIr/My.KmKXJehySBD5LelKDQlM40OheAn1eg/fUJVhS',NULL,'071749206567',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 08:54:22','2025-12-30 08:54:22'),(24,'ujkhgkj khooioio','info88@imaginenation.co.ke',NULL,NULL,'$2y$12$Ml3DtbxN0B.2jASFkJGjhObXz5l8Ksj05CzzCi/bNdPxe9ckf5MeC',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 12:54:47','2025-12-30 12:54:47'),(25,'Dennis Kibet','info656@imaginenation.co.ke',NULL,NULL,'$2y$12$xvaZm5UwrR2r7XD5W.SXt.D4x0.S.J5z3bUsZwlXJbaxU7.9e0.2O',NULL,'07175592048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 13:46:08','2025-12-30 13:46:08'),(26,'Dennis Kibet','kibeettdennis@gmail.com',NULL,NULL,'$2y$12$RxFt0rvXQ/VhP0Xd/caRCuZGRZCSmVZfKSllaZygsNrTpcccEFFWO',NULL,'0717492058',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 14:00:32','2025-12-30 14:00:32'),(27,'Dennis Kibet','kibetttdennis@gmail.com',NULL,NULL,'$2y$12$XP24cvfTq1m/I7q5bYWRI.PSsAqD8XyNz0RxvQbdBgolBdRADXmWK',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 14:05:01','2025-12-30 14:05:01'),(28,'Dennis Kibet','karleighddddeno@gmail.com',NULL,NULL,'$2y$12$.euaaxDqsN0d7Z2S1okV2.eX6tLG2WvcR92/.N/jwGC/b4NRh6Mx6',NULL,'0717662048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 16:53:52','2025-12-30 16:53:52'),(29,'Dennis Kibet','ur0bancreationslive@gmail.com',NULL,NULL,'$2y$12$aE1GNjgxnYmyjWsOrrBKn.zx1sS9g/Ns3Obdd.9NXLvUSL28tJpPi',NULL,'07178949204',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 16:57:03','2025-12-30 16:57:03'),(30,'Dennis Kibetytrt','karleighdennnno@gmail.com',NULL,NULL,'$2y$12$I5CDBogMu4OME5EQmd2q3OYyME2EQ2etsx6AAen12oUu.ZYfRRNjq',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 16:58:40','2025-12-30 16:58:40'),(31,'dejgyu itituu','kguigui@kgj.com',NULL,NULL,'$2y$12$TUsvXBZZlnqNfFZgTclj7.0nmzDSLdYdngnEXQwkkLKDfy4/Ps6Nm',NULL,'0096775455',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:00:54','2025-12-30 17:00:54'),(32,'Dennis Koech','info@oimaginenation.co.ke',NULL,NULL,'$2y$12$9CyPxMcJVgGdJDZmGKr9Z.9zYau3PxJM1IvldeemrKqNN6MkmSgVq',NULL,'07174928888048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:15:27','2025-12-30 17:15:27'),(34,'Dennis Kibet','karleighden0098io@gmail.com',NULL,NULL,'$2y$12$fAq5SXHFGEJbUXX72GXkkOJKieUz/pxifOs4aGqQtjkYHs0rNas0.',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:21:03','2025-12-30 17:21:03'),(35,'Dennis Koech','info@iiiimaginenation.co.ke',NULL,NULL,'$2y$12$B5n5bggx1srZ7n/KMBXfy.Aj6/gidUOjbr3u4u0aGCm67X5NPkJTG',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:23:07','2025-12-30 17:23:07'),(37,'Dennis Kibet','karllllleighdeno@gmail.com',NULL,NULL,'$2y$12$MUMVGVAGxKViqKRt2g1RfO6IAFbudSXW9O2UWaOB6sPLekHYF25py',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:31:54','2025-12-30 17:31:54'),(39,'Dennis Koech','info@imaginijienation.co.ke',NULL,NULL,'$2y$12$nNqoAZWQYNeh3zc5aIP2s.DgZQdHrEddcuz0y3ByhCQs.qgzx5LrC',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:37:49','2025-12-30 17:37:49'),(41,'Dennis Kibet','urbaoooncreationslive@gmail.com',NULL,NULL,'$2y$12$8cf8g2hv0eSkBFkMgbF.3uYUxBTESSkZ14DhNoLpp80qVXL1.doJm',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:39:11','2025-12-30 17:39:11'),(42,'Dennis Koech','info@imagineniuynation.co.ke',NULL,NULL,'$2y$12$pq.wIfGtyDkuMa8.cU2sJ.9TF4xW7QSN0cWWgpD1QeUzbLO1Yk7z.',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 17:41:35','2025-12-30 17:41:35'),(43,'Dennis Koech','info@imaginbhenation.co.ke',NULL,NULL,'$2y$12$tT353I4lKg7aRy2CHy7Muu5lFhtmKk3W6f514VRQM0.Bkxn8fKI7m',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-30 18:18:31','2025-12-30 18:18:31'),(44,'Dennis Kibet','kaleighdeno@gmail.com',NULL,NULL,'$2y$12$QH59dEwYKs6A4wLulNKYJ.9SlZY53szVY9vk.OxDOkiymRvnfslRq',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-31 02:33:22','2025-12-31 02:33:22'),(46,'Dennis Koech','info78@imaginenation.co.ke',NULL,NULL,'$2y$12$py/yQ0I3OtM95EgwcRhqb.GBmyGWHziWsUhiBEOaObHe./oApl7je',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-31 03:57:27','2025-12-31 03:57:27'),(47,'Dennis Kibet','info6567@imaginenation.co.ke',NULL,NULL,'$2y$12$uSVHHCHyfYrWymaaCiHiq.Lhhc5pdjfYoeMJd3shzMu8XEG/ys9mm',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-31 05:15:31','2025-12-31 05:15:31'),(48,'Dennis Kibet','kibettdennis@gmmmail.com',NULL,NULL,'$2y$12$2hKB0Ud7uDZZo0Hv0Opv8eWsaXEQpBvZd3EU/E98ukThPK1LoEO2O',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-31 05:45:47','2025-12-31 05:45:47'),(49,'Dennis Kibet','info@imaginenationnn.co.ke',NULL,NULL,'$2y$12$Umu.Zu3cBpQkVfhLeHfAe.z7Wjlf.ztssXNawq2OhlC4br4HXaNCy',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-31 05:49:12','2025-12-31 05:49:12'),(50,'Dennisdstdfuiopj Kibet','kibettd7chgennis@gmail.com',NULL,NULL,'$2y$12$BMn4MZLBaL3kWa5EiTpNTuzQnq3CmC7ogjURgSSnOJ1zwUWau2.Va',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2025-12-31 06:28:56','2025-12-31 06:28:56'),(51,'Dennis Kibet','inf@imaginenation.co.ke',NULL,NULL,'$2y$12$MQc6uJ7JmrTGNsnVU62Rhu.YMOXPWNG23DHwYGHfZe4aWkyqT6RIq',NULL,'0717492048',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2026-01-01 08:06:50','2026-01-01 08:06:50'),(52,'Gideon Boot Kidian','kidianboot@gmail.com',NULL,NULL,'$2y$12$/1L7LdUYLmBk1BcWztGFWuJOP0G1VLOPn1.opb83hhJDYq144P01u',NULL,'0712345678',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2026-01-01 12:01:30','2026-01-01 12:01:30'),(53,'New Guest','newguest@guest.com',NULL,NULL,'$2y$12$WV.v5knIZD/yMkjHe/mrfuGefbJNsXEQIw1aOE0J/hajtSA60nC6C',NULL,'07001122334',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'active',NULL,'2026-01-04 10:27:43','2026-01-04 10:27:43');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-06 23:05:53
