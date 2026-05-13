-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: kwikshift
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_notifications`
--

DROP TABLE IF EXISTS `activity_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(80) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(80) NOT NULL DEFAULT 'bell',
  `severity` varchar(40) NOT NULL DEFAULT 'info',
  `related_type` varchar(255) DEFAULT NULL,
  `related_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_notifications_related_type_related_id_index` (`related_type`,`related_id`),
  KEY `activity_notifications_user_id_foreign` (`user_id`),
  KEY `activity_notifications_type_index` (`type`),
  KEY `activity_notifications_severity_index` (`severity`),
  KEY `activity_notifications_occurred_at_index` (`occurred_at`),
  KEY `activity_notifications_read_at_index` (`read_at`),
  CONSTRAINT `activity_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_notifications`
--

LOCK TABLES `activity_notifications` WRITE;
/*!40000 ALTER TABLE `activity_notifications` DISABLE KEYS */;
INSERT INTO `activity_notifications` VALUES (1,'quote_request_updated','Quote marked Created','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 13:21:06','2026-05-10 10:21:06','2026-05-10 10:20:46','2026-05-10 10:21:06'),(2,'quote_request','New quote request','roy okoth - londiani rd to kirinyaga rd','http://127.0.0.1:8000/quotes/2','message-square-quote','primary','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 13:48:28','2026-05-10 10:48:28','2026-05-10 10:48:28','2026-05-10 10:48:28'),(3,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-10 14:13:32','2026-05-10 14:13:32','2026-05-10 10:53:27','2026-05-10 14:13:32'),(4,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-10 14:13:32','2026-05-10 14:13:32','2026-05-10 10:53:51','2026-05-10 14:13:32'),(5,'quote_request_updated','Quote marked Approved','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 13:57:51','2026-05-10 10:57:51','2026-05-10 10:54:32','2026-05-10 10:57:51'),(6,'quote_request_updated','Quote marked Processing','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 13:57:51','2026-05-10 10:57:51','2026-05-10 10:54:38','2026-05-10 10:57:51'),(7,'quote_request_updated','Quote marked Created','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 13:57:51','2026-05-10 10:57:51','2026-05-10 10:55:08','2026-05-10 10:57:51'),(8,'quote_request_updated','Quote marked Emailed','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 13:57:51','2026-05-10 10:57:51','2026-05-10 10:55:25','2026-05-10 10:57:51'),(9,'quote_request_updated','Quote marked Created','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:07:57','2026-05-10 11:07:57','2026-05-10 11:03:34','2026-05-10 11:07:57'),(10,'quote_request_updated','Quote marked Approved','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:16:25','2026-05-10 14:16:25','2026-05-10 14:16:24','2026-05-10 14:16:25'),(11,'quote_request_updated','Quote marked Approved','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 14:16:34','2026-05-10 14:16:34','2026-05-10 14:16:34','2026-05-10 14:16:34'),(12,'quote_request_updated','Quote marked Processing','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:18:19','2026-05-10 14:18:19','2026-05-10 14:18:11','2026-05-10 14:18:19'),(13,'quote_request_updated','Quote marked Created','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:19:46','2026-05-10 14:19:46','2026-05-10 14:18:43','2026-05-10 14:19:46'),(14,'quote_request_updated','Quote marked Emailed','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:19:46','2026-05-10 14:19:46','2026-05-10 14:18:50','2026-05-10 14:19:46'),(15,'quote_request_updated','Quote marked Created','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:21:47','2026-05-10 14:21:47','2026-05-10 14:20:43','2026-05-10 14:21:47'),(16,'quote_request_updated','Quote marked Emailed','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 14:24:03','2026-05-10 14:24:03','2026-05-10 14:21:56','2026-05-10 14:24:03'),(17,'quote_request_updated','Quote marked Rejected','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 14:35:44','2026-05-10 14:35:44','2026-05-10 14:35:44','2026-05-10 14:35:44'),(18,'quote_request_updated','Quote marked New','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 14:36:47','2026-05-10 14:36:47','2026-05-10 14:36:47','2026-05-10 14:36:47'),(19,'quote_request_updated','Quote marked Approved','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 14:42:20','2026-05-10 14:42:20','2026-05-10 14:36:54','2026-05-10 14:42:20'),(20,'quote_request_updated','Quote marked Processing','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 14:42:20','2026-05-10 14:42:20','2026-05-10 14:36:56','2026-05-10 14:42:20'),(21,'quote_request_updated','Quote marked Created','cyrus kipruto kirop - #QT00001','http://127.0.0.1:8000/quotes/1','message-square-quote','info','App\\Models\\QuoteRequest',1,1,NULL,'2026-05-10 14:42:20','2026-05-10 14:42:20','2026-05-10 14:37:29','2026-05-10 14:42:20'),(22,'quote_request_updated','Quote marked Email Failed','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 15:21:56','2026-05-10 15:21:56','2026-05-10 15:21:37','2026-05-10 15:21:56'),(23,'quote_request_updated','Quote marked Emailed','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-10 15:28:10','2026-05-10 15:28:10','2026-05-10 15:25:04','2026-05-10 15:28:10'),(24,'login_failed','Failed login attempt','admin@kwikshift.co.ke from 127.0.0.1','http://127.0.0.1:8000/login','shield-alert','warning',NULL,NULL,NULL,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-10 15:37:38','2026-05-10 15:37:38','2026-05-10 15:37:03','2026-05-10 15:37:38'),(25,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-10 15:37:38','2026-05-10 15:37:38','2026-05-10 15:37:15','2026-05-10 15:37:38'),(26,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-11 12:56:49','2026-05-11 12:56:49','2026-05-11 11:12:29','2026-05-11 12:56:49'),(27,'account_activity','Two-factor enabled','hydrasoftke@gmail.com enabled two-factor authentication.','http://127.0.0.1:8000/account','shield-check','info','App\\Models\\User',1,1,NULL,'2026-05-11 12:58:16','2026-05-11 12:58:16','2026-05-11 12:58:16','2026-05-11 12:58:16'),(28,'login_success','Login successful','hydrasoftke@gmail.com from 192.168.6.7','http://192.168.6.3:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"192.168.6.7\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/148.0.0.0 Safari\\/537.36\"}','2026-05-11 13:08:50','2026-05-11 13:08:50','2026-05-11 13:05:08','2026-05-11 13:08:50'),(29,'login_success','Login successful','hydrasoftke@gmail.com from 192.168.6.7','http://192.168.6.3:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"192.168.6.7\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/148.0.0.0 Safari\\/537.36\"}','2026-05-11 13:08:50','2026-05-11 13:08:50','2026-05-11 13:08:29','2026-05-11 13:08:50'),(30,'quote_request','New quote request','john doe - Kasarani, Nairobi, Nairobi County, Kenya to Uthiru, Waithaka division, Dagoretti, Nairobi, Nairobi County, 29039, Kenya','http://192.168.6.3:8000/quotes/3','message-square-quote','primary','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 13:16:26','2026-05-11 13:16:26','2026-05-11 13:16:26','2026-05-11 13:16:26'),(31,'quote_request_updated','Quote marked Approved','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 13:19:14','2026-05-11 13:19:14','2026-05-11 13:18:26','2026-05-11 13:19:14'),(32,'quote_request_updated','Quote marked Processing','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 13:19:14','2026-05-11 13:19:14','2026-05-11 13:18:34','2026-05-11 13:19:14'),(33,'account_activity','Account profile updated','hydrasoftke@gmail.com updated profile details.','http://192.168.6.3:8000/account','circle-user','info','App\\Models\\User',1,1,NULL,'2026-05-11 13:20:59','2026-05-11 13:20:59','2026-05-11 13:20:58','2026-05-11 13:20:59'),(34,'quote_request_updated','Quote marked Approved','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 14:17:53','2026-05-11 14:17:53','2026-05-11 13:21:12','2026-05-11 14:17:53'),(35,'quote_request_updated','Quote marked Processing','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 14:17:53','2026-05-11 14:17:53','2026-05-11 13:21:23','2026-05-11 14:17:53'),(36,'quote_request_updated','Quote marked Created','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 14:17:53','2026-05-11 14:17:53','2026-05-11 13:22:16','2026-05-11 14:17:53'),(37,'quote_request_updated','Quote marked Emailed','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 14:17:53','2026-05-11 14:17:53','2026-05-11 13:23:03','2026-05-11 14:17:53'),(38,'quote_request_updated','Quote marked Created','john doe - #QT00003','http://192.168.6.3:8000/quotes/3','message-square-quote','info','App\\Models\\QuoteRequest',3,1,NULL,'2026-05-11 14:17:53','2026-05-11 14:17:53','2026-05-11 13:56:58','2026-05-11 14:17:53'),(39,'login_failed','Failed login attempt','admin@kwikshift.co.ke from 127.0.0.1','http://127.0.0.1:8000/login','shield-alert','warning',NULL,NULL,NULL,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-11 21:48:04','2026-05-11 21:48:04','2026-05-11 21:46:35','2026-05-11 21:48:04'),(40,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-11 21:48:04','2026-05-11 21:48:04','2026-05-11 21:47:54','2026-05-11 21:48:04'),(41,'message_sent','Message sent','follow up on our quotation to hydrasoftke@gmail.com','http://127.0.0.1:8000/messages/4','send','success','App\\Models\\Message',4,1,NULL,'2026-05-11 21:49:33','2026-05-11 21:49:33','2026-05-11 21:49:20','2026-05-11 21:49:33'),(42,'quote_request_updated','Quote marked Created','roy okoth - #QT00002','http://127.0.0.1:8000/quotes/2','message-square-quote','info','App\\Models\\QuoteRequest',2,1,NULL,'2026-05-11 22:44:26','2026-05-11 22:44:26','2026-05-11 22:22:40','2026-05-11 22:44:26'),(43,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-11 23:07:25','2026-05-11 23:07:25','2026-05-11 23:07:10','2026-05-11 23:07:25'),(44,'login_success','Login successful','hydrasoftke@gmail.com from 127.0.0.1','http://127.0.0.1:8000/account','log-in','success','App\\Models\\User',1,1,'{\"ip\":\"127.0.0.1\",\"user_agent\":\"Mozilla\\/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko\\/20100101 Firefox\\/150.0\"}','2026-05-12 21:45:58','2026-05-12 21:45:58','2026-05-12 21:45:47','2026-05-12 21:45:58'),(45,'account_activity','Two-factor disabled','hydrasoftke@gmail.com disabled two-factor authentication.','http://127.0.0.1:8000/account','shield-x','info','App\\Models\\User',1,1,NULL,'2026-05-12 21:46:11','2026-05-12 21:46:11','2026-05-12 21:46:11','2026-05-12 21:46:11'),(46,'quote_request','New quote request','denica - Ruiru, Kiambu, Kenya to Landless Road, Kamenu ward, Thika Town, Kiambu, Kenya','http://127.0.0.1:8000/quotes/4','message-square-quote','primary','App\\Models\\QuoteRequest',4,1,NULL,'2026-05-12 21:48:37','2026-05-12 21:48:37','2026-05-12 21:48:37','2026-05-12 21:48:37'),(47,'quote_request_updated','Quote marked Approved','denica - #QT00004','http://127.0.0.1:8000/quotes/4','message-square-quote','info','App\\Models\\QuoteRequest',4,1,NULL,'2026-05-12 21:50:15','2026-05-12 21:50:15','2026-05-12 21:48:41','2026-05-12 21:50:15'),(48,'quote_request_updated','Quote marked Processing','denica - #QT00004','http://127.0.0.1:8000/quotes/4','message-square-quote','info','App\\Models\\QuoteRequest',4,1,NULL,'2026-05-12 21:50:15','2026-05-12 21:50:15','2026-05-12 21:48:43','2026-05-12 21:50:15'),(49,'quote_request_updated','Quote marked Created','denica - #QT00004','http://127.0.0.1:8000/quotes/4','message-square-quote','info','App\\Models\\QuoteRequest',4,1,NULL,'2026-05-12 21:50:15','2026-05-12 21:50:15','2026-05-12 21:49:03','2026-05-12 21:50:15'),(50,'quote_request_updated','Quote marked Emailed','denica - #QT00004','http://127.0.0.1:8000/quotes/4','message-square-quote','info','App\\Models\\QuoteRequest',4,1,NULL,'2026-05-12 21:50:15','2026-05-12 21:50:15','2026-05-12 21:49:16','2026-05-12 21:50:15'),(51,'quote_approved','Quote approved by denica','#QT00004','http://127.0.0.1:8000/quotes/4','badge-check','success','App\\Models\\QuoteRequest',4,NULL,NULL,'2026-05-12 21:50:15','2026-05-12 21:50:15','2026-05-12 21:49:38','2026-05-12 21:50:15'),(52,'quote_request','New quote request','denica - Londiani ward, Kipkelion East, Kericho County, Kenya to Junction Maili Mbili, Hells Gate ward, Naivasha, Nakuru, Kenya','http://127.0.0.1:8000/quotes/5','message-square-quote','primary','App\\Models\\QuoteRequest',5,1,NULL,'2026-05-12 22:00:23','2026-05-12 22:00:23','2026-05-12 22:00:22','2026-05-12 22:00:23'),(53,'quote_request_updated','Quote marked Approved','denica - #QT00005','http://127.0.0.1:8000/quotes/5','message-square-quote','info','App\\Models\\QuoteRequest',5,1,NULL,'2026-05-12 22:01:50','2026-05-12 22:01:50','2026-05-12 22:00:26','2026-05-12 22:01:50'),(54,'quote_request_updated','Quote marked Processing','denica - #QT00005','http://127.0.0.1:8000/quotes/5','message-square-quote','info','App\\Models\\QuoteRequest',5,1,NULL,'2026-05-12 22:01:50','2026-05-12 22:01:50','2026-05-12 22:00:30','2026-05-12 22:01:50'),(55,'quote_request_updated','Quote marked Created','denica - #QT00005','http://127.0.0.1:8000/quotes/5','message-square-quote','info','App\\Models\\QuoteRequest',5,1,NULL,'2026-05-12 22:01:50','2026-05-12 22:01:50','2026-05-12 22:00:51','2026-05-12 22:01:50'),(56,'quote_request_updated','Quote marked Emailed','denica - #QT00005','http://127.0.0.1:8000/quotes/5','message-square-quote','info','App\\Models\\QuoteRequest',5,1,NULL,'2026-05-12 22:01:50','2026-05-12 22:01:50','2026-05-12 22:01:03','2026-05-12 22:01:50'),(57,'quote_approved','Quote approved by denica','#QT00005','http://127.0.0.1:8000/quotes/5','badge-check','success','App\\Models\\QuoteRequest',5,NULL,NULL,'2026-05-12 22:01:50','2026-05-12 22:01:50','2026-05-12 22:01:28','2026-05-12 22:01:50');
/*!40000 ALTER TABLE `activity_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` longtext DEFAULT NULL,
  `is_secret` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_group_key_unique` (`group`,`key`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'company','name','KwikShift Movers & Relocators',0,'2026-05-09 15:46:16','2026-05-09 15:46:16'),(2,'company','email','info@kwikshiftmovers.co.ke',0,'2026-05-09 15:46:16','2026-05-09 15:46:16'),(3,'company','phone','+254 112587581 / +254111330980',0,'2026-05-09 15:46:16','2026-05-09 15:46:16'),(4,'company','address_line_1','Londiani Road, off Likoni Road',0,'2026-05-09 15:46:16','2026-05-09 15:46:16'),(5,'company','address_line_2','Industrial Area, Nairobi, 00200, KE',0,'2026-05-09 15:46:16','2026-05-09 15:46:16'),(6,'company','logo_path','images/logo-dark.png',0,'2026-05-09 15:46:16','2026-05-09 15:46:16'),(7,'email','enabled','1',0,'2026-05-10 07:30:06','2026-05-10 07:30:40'),(8,'email','provider','smtp',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(9,'email','from_name','Kwikshift Movers',0,'2026-05-10 07:30:07','2026-05-10 07:30:40'),(10,'email','from_address','noreply@fumiklincleaners.com',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(11,'email','smtp_host','smtp-relay.brevo.com',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(12,'email','smtp_port','587',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(13,'email','smtp_encryption','tls',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(14,'email','smtp_username','9bad20001@smtp-brevo.com',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(15,'email','mail_from_messages_name','Kwikshift Movers',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(16,'email','mail_from_messages_address','info@fumiklincleaners.com',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(17,'email','mail_from_noreply_name','Kwikshift Movers',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(18,'email','mail_from_noreply_address','noreply@fumiklincleaners.com',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(19,'email','mail_from_invoices_name','Kwikshift Movers',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(20,'email','mail_from_invoices_address','sales@fumiklincleaners.com',0,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(21,'email','smtp_password','eyJpdiI6Ikw2Zm8rNWIrVWM1REVHR3B5YXAwUmc9PSIsInZhbHVlIjoieTlIUnR3Vk5uQ2tVS1BheHQzSXE3R3cwTmRVRWdPclJrWjgwem9nRHNGWnZqMHJsVUZDSkJGR1FKcE5uVDl1Y2ZKNVBNMll2QU5veUFKOWlmWkFDMzMvNkdMaUxLMng1NUxBUVhDbHM1NFBhR0U1RWhFbldvTkwwbTRvKzM3SmYiLCJtYWMiOiI4Y2NkNzJkYWU2MmQ0OWNlM2ExZmVjNzhiMmIyYzlkZTcwNDY4MzE5MDI0YzgxYjdjMGM1OWUyYTVjYTYyMjlkIiwidGFnIjoiIn0=',1,'2026-05-10 07:30:07','2026-05-10 07:30:07'),(22,'payments','mpesa_enabled','1',0,'2026-05-10 10:28:45','2026-05-10 10:29:14'),(23,'payments','mpesa_type','paybill',0,'2026-05-10 10:28:45','2026-05-10 15:11:58'),(24,'payments','cash_enabled','0',0,'2026-05-10 10:28:45','2026-05-10 10:28:57'),(25,'payments','cash_instruction','Pay cash on day of move to our representative.',0,'2026-05-10 10:28:45','2026-05-10 10:28:45'),(26,'payments','bank_enabled','0',0,'2026-05-10 10:28:45','2026-05-10 10:28:45'),(27,'payments','bank_name','',0,'2026-05-10 10:28:45','2026-05-10 10:28:45'),(28,'payments','bank_account_name','',0,'2026-05-10 10:28:45','2026-05-10 10:28:45'),(29,'payments','bank_branch','',0,'2026-05-10 10:28:46','2026-05-10 10:28:46'),(30,'payments','bank_swift_code','',0,'2026-05-10 10:28:46','2026-05-10 10:28:46'),(31,'payments','mpesa_till_account_name','',0,'2026-05-10 10:28:46','2026-05-10 10:28:46'),(32,'payments','mpesa_paybill_account_name','KWIKSHIFT MOVERS LTD',0,'2026-05-10 10:28:46','2026-05-10 15:11:58'),(33,'payments','mpesa_pochi_registered_name','KWIKSHIFT MOVERS',0,'2026-05-10 10:28:46','2026-05-10 10:28:46'),(34,'payments','mpesa_pochi_phone','eyJpdiI6Inc3U2JFcUpNRlVpWVNYN25WN2k5Znc9PSIsInZhbHVlIjoiVEJYRW5wcWdqZ28yeHlVbTFjeGdIZz09IiwibWFjIjoiZmQ4M2VjYzRjZGVlMDczZjAzNjAyNjFmN2ZkNzIyZDI2MDk1MzBlYzU0ODBlZDY2OTJjN2MzMmVmZTE5ZmFiMSIsInRhZyI6IiJ9',1,'2026-05-10 10:28:46','2026-05-10 10:28:46'),(35,'invoice','thank_you_message','Thank you for choosing {company_name}. We appreciate your business and look forward to serving you again. For any queries regarding this invoice, please contact us at {company_email} or {company_phone}.',0,'2026-05-10 10:28:46','2026-05-10 10:28:46'),(36,'payments','mpesa_paybill_business_number','eyJpdiI6IlNXU3BSNDBjTVBJNXNVK1VLNU1DZEE9PSIsInZhbHVlIjoid0JuZ1kvd0FOaHBWM0l2UHQydmlYZz09IiwibWFjIjoiMWJkMGU1MTYzMjQ3YjE0NGU1NTcxMDgzZDZiMWMzMjE4YjRlYzlhZGI1MWJkMDBhYTA5OTgzNTI3MTRiYzE5YyIsInRhZyI6IiJ9',1,'2026-05-10 15:11:58','2026-05-11 14:04:20'),(37,'payments','mpesa_paybill_account_number','eyJpdiI6IjBkMVZmcVhVVmIvaDJISjdSSkFUTUE9PSIsInZhbHVlIjoiUXgrOUJkelJwbFhnak1WbERLdUtEZz09IiwibWFjIjoiOWY2ZjNkYzAwYzhlNjQ1NjZhMWVjNWQxMGU0NDdhN2Y0ZWNmNDIyYTQ1NjcyNTZkYzIzZTJjZTNlOTU5YjE1MyIsInRhZyI6IiJ9',1,'2026-05-10 15:11:59','2026-05-10 15:11:59'),(38,'company','website','https://kwikshiftmovers.co.ke/',0,'2026-05-12 21:54:55','2026-05-12 21:54:55'),(39,'company','business_registration_number','BN-QBSOJEB3',0,'2026-05-12 21:54:55','2026-05-12 21:54:55'),(40,'company','authorized_representative_name','Roy Okoth',0,'2026-05-12 21:54:55','2026-05-12 21:54:55'),(41,'company','authorized_representative_title','Operations Manager',0,'2026-05-12 21:54:55','2026-05-12 21:54:55'),(42,'company','liability_cap_amount','5000',0,'2026-05-12 21:54:55','2026-05-12 21:54:55');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `booking_stages`
--

DROP TABLE IF EXISTS `booking_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking_stages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stageable_type` varchar(255) NOT NULL,
  `stageable_id` bigint(20) unsigned NOT NULL,
  `stage` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `triggered_by` enum('system','admin','customer') NOT NULL DEFAULT 'system',
  `actor_name` varchar(255) DEFAULT NULL,
  `actor_ip` varchar(255) DEFAULT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_stages_stageable_type_stageable_id_index` (`stageable_type`,`stageable_id`),
  KEY `booking_stages_stage_index` (`stage`),
  KEY `booking_stages_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking_stages`
--

LOCK TABLES `booking_stages` WRITE;
/*!40000 ALTER TABLE `booking_stages` DISABLE KEYS */;
INSERT INTO `booking_stages` VALUES (1,'App\\Models\\Quotation',1,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','cyrus kipruto kirop','127.0.0.1','online','[]','2026-05-10 10:20:26'),(2,'App\\Models\\Quotation',1,'APPROVED_ONLINE','Quotation approved online by cyrus kipruto kirop','customer','cyrus kipruto kirop','127.0.0.1','online','{\"agreement\":true}','2026-05-10 10:20:46'),(3,'App\\Models\\Quotation',1,'DEPOSIT_PENDING','Deposit of KES 25,400.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-10 10:20:46'),(4,'App\\Models\\Invoice',1,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-10 10:35:12'),(5,'App\\Models\\QuoteRequest',2,'REQUEST_SUBMITTED','Quote request created by admin','admin','Cyrus Kirop','127.0.0.1','system','[]','2026-05-10 10:48:28'),(6,'App\\Models\\Quotation',2,'QUOTE_CREATED','Quotation created by admin','admin','Cyrus Kirop',NULL,'system','[]','2026-05-10 10:55:08'),(7,'App\\Models\\Quotation',2,'QUOTE_SENT','Quotation sent via whatsapp','admin','Cyrus Kirop',NULL,'whatsapp','[]','2026-05-10 10:55:25'),(8,'App\\Models\\Quotation',2,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','roy okoth','127.0.0.1','online','[]','2026-05-10 10:59:26'),(9,'App\\Models\\Quotation',2,'APPROVED_ONLINE','Quotation approved online by roy okoth','customer','roy okoth','127.0.0.1','online','{\"agreement\":true}','2026-05-10 11:03:34'),(10,'App\\Models\\Quotation',2,'DEPOSIT_PENDING','Deposit of KES 6,250.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-10 11:03:34'),(11,'App\\Models\\Quotation',2,'DEPOSIT_RECEIVED','Deposit received - KES 6,250.00 - Ref: qrewjjjgh via mpesa','admin','Cyrus Kirop',NULL,NULL,'[]','2026-05-10 11:05:31'),(12,'App\\Models\\Quotation',2,'BOOKING_CONFIRMED','Booking confirmed after deposit receipt','system',NULL,NULL,NULL,'[]','2026-05-10 11:05:32'),(13,'App\\Models\\Invoice',2,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-10 11:08:33'),(14,'App\\Models\\Invoice',2,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-10 14:13:06'),(15,'App\\Models\\Quotation',3,'QUOTE_CREATED','Quotation created by admin','admin','Cyrus Kirop',NULL,'system','[]','2026-05-10 14:18:43'),(16,'App\\Models\\Quotation',3,'QUOTE_SENT','Quotation sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-10 14:18:50'),(17,'App\\Models\\Quotation',3,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','roy okoth','127.0.0.1','online','[]','2026-05-10 14:20:33'),(18,'App\\Models\\Quotation',3,'APPROVED_ONLINE','Quotation approved online by roy okoth','customer','roy okoth','127.0.0.1','online','{\"agreement\":true}','2026-05-10 14:20:43'),(19,'App\\Models\\Quotation',3,'DEPOSIT_PENDING','Deposit of KES 625.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-10 14:20:43'),(20,'App\\Models\\Quotation',3,'QUOTE_SENT','Quotation sent via whatsapp','admin','Cyrus Kirop',NULL,'whatsapp','[]','2026-05-10 14:21:56'),(21,'App\\Models\\Invoice',3,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-10 14:22:41'),(22,'App\\Models\\Invoice',4,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-10 14:30:31'),(23,'App\\Models\\Quotation',4,'QUOTE_CREATED','Quotation created by admin','admin','Cyrus Kirop',NULL,'system','[]','2026-05-10 14:37:29'),(24,'App\\Models\\Quotation',3,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','roy okoth','127.0.0.1','online','[]','2026-05-10 14:55:28'),(25,'App\\Models\\Quotation',3,'QUOTE_SENT','Quotation sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-10 15:25:04'),(26,'App\\Models\\Invoice',5,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-10 16:21:22'),(27,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 16:29:30'),(28,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 18:27:44'),(29,'App\\Models\\Quotation',3,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','roy okoth','127.0.0.1','online','[]','2026-05-10 18:30:22'),(30,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 20:32:35'),(31,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 21:31:21'),(32,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 21:47:06'),(33,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 21:51:50'),(34,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-10 23:58:40'),(35,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 11:13:12'),(36,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 11:13:15'),(37,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 12:08:36'),(38,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 12:29:59'),(39,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 12:41:37'),(40,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 12:48:18'),(41,'App\\Models\\Quotation',3,'PDF_DOWNLOADED','Quote PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 12:53:12'),(42,'App\\Models\\Invoice',5,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 12:53:37'),(43,'App\\Models\\QuoteRequest',3,'REQUEST_SUBMITTED','Quote request created by admin','admin','Cyrus Kirop','192.168.6.7','system','[]','2026-05-11 13:16:26'),(44,'App\\Models\\Quotation',5,'QUOTE_CREATED','Quotation created by admin','admin','Cyrus Kirop',NULL,'system','[]','2026-05-11 13:22:16'),(45,'App\\Models\\Quotation',5,'QUOTE_SENT','Quotation sent via whatsapp','admin','Cyrus Kirop',NULL,'whatsapp','[]','2026-05-11 13:23:03'),(46,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:24:09'),(47,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:24:28'),(48,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:27:25'),(49,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:33:38'),(50,'App\\Models\\Quotation',5,'QUOTE_SENT','Quotation sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 13:38:13'),(51,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:43:32'),(52,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:44:53'),(53,'App\\Models\\Quotation',5,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','john doe','192.168.6.7','online','[]','2026-05-11 13:55:28'),(54,'App\\Models\\Quotation',5,'APPROVED_ONLINE','Quotation approved online by john doe','customer','john doe','192.168.6.7','online','{\"agreement\":true}','2026-05-11 13:56:59'),(55,'App\\Models\\Quotation',5,'DEPOSIT_PENDING','Deposit of KES 12,500.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-11 13:56:59'),(56,'App\\Models\\Quotation',5,'DEPOSIT_RECEIVED','Deposit received - KES 12,500.00 - Ref: udsdjs via mpesa','admin','Cyrus Kirop',NULL,NULL,'[]','2026-05-11 13:59:47'),(57,'App\\Models\\Quotation',5,'BOOKING_CONFIRMED','Booking confirmed after deposit receipt','system',NULL,NULL,NULL,'[]','2026-05-11 13:59:47'),(58,'App\\Models\\Invoice',6,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 14:02:42'),(59,'App\\Models\\Invoice',6,'PDF_DOWNLOADED','Invoice PDF downloaded','admin','Cyrus Kirop',NULL,'download','[]','2026-05-11 14:03:13'),(60,'App\\Models\\Invoice',6,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 14:05:32'),(61,'App\\Models\\Invoice',6,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-11 14:08:24'),(62,'App\\Models\\Invoice',4,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 22:08:47'),(63,'App\\Models\\Invoice',4,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-11 22:10:50'),(64,'App\\Models\\Quotation',3,'QUOTE_SENT','Quotation sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 22:21:12'),(65,'App\\Models\\Quotation',3,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','roy okoth','127.0.0.1','online','[]','2026-05-11 22:22:35'),(66,'App\\Models\\Quotation',3,'APPROVED_ONLINE','Quotation approved online by roy okoth','customer','roy okoth','127.0.0.1','online','{\"agreement\":true}','2026-05-11 22:22:41'),(67,'App\\Models\\Quotation',3,'DEPOSIT_PENDING','Deposit of KES 625.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-11 22:22:41'),(68,'App\\Models\\Quotation',3,'DEPOSIT_RECEIVED','Deposit received - KES 625.00 - Ref: MPESA CONFIRMATION via mpesa','admin','Cyrus Kirop',NULL,NULL,'[]','2026-05-11 22:43:53'),(69,'App\\Models\\Quotation',3,'BOOKING_CONFIRMED','Booking confirmed after deposit receipt','system',NULL,NULL,NULL,'[]','2026-05-11 22:43:53'),(70,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 22:44:40'),(72,'App\\Models\\Invoice',7,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-11 22:49:50'),(73,'App\\Models\\Invoice',7,'MOVE_COMPLETED','Associated move marked as completed after full invoice payment','system',NULL,NULL,'system','[]','2026-05-11 22:49:50'),(74,'App\\Models\\Invoice',7,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-11 23:00:00'),(75,'App\\Models\\Invoice',7,'MOVE_COMPLETED','Associated move marked as completed after full invoice payment','system',NULL,NULL,'system','[]','2026-05-11 23:00:00'),(76,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:03:32'),(77,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:07:35'),(78,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:09:18'),(79,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:09:53'),(80,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:12:57'),(81,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:15:21'),(82,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:16:47'),(83,'App\\Models\\Invoice',7,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-11 23:17:55'),(84,'App\\Models\\Invoice',7,'PAYMENT_RECEIVED','Invoice marked as paid','admin','Cyrus Kirop',NULL,'system','[]','2026-05-11 23:26:54'),(85,'App\\Models\\Invoice',7,'MOVE_COMPLETED','Associated move marked as completed after full invoice payment','system',NULL,NULL,'system','[]','2026-05-11 23:26:54'),(86,'App\\Models\\Invoice',7,'PAYMENT_NOTIFICATION_SENT','Payment completion email sent','admin','Cyrus Kirop',NULL,'email','{\"recipient\":\"hydrasoftke@gmail.com\",\"message_id\":\"26407597e42b25f34ad4dd3272248e73@fumiklincleaners.com\"}','2026-05-11 23:27:01'),(87,'App\\Models\\QuoteRequest',4,'REQUEST_SUBMITTED','Quote request created by admin','admin','Cyrus Kirop','127.0.0.1','system','[]','2026-05-12 21:48:37'),(88,'App\\Models\\Quotation',6,'QUOTE_CREATED','Quotation created by admin','admin','Cyrus Kirop',NULL,'system','[]','2026-05-12 21:49:03'),(89,'App\\Models\\Quotation',6,'QUOTE_SENT','Quotation sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-12 21:49:16'),(90,'App\\Models\\Quotation',6,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','denica','127.0.0.1','online','[]','2026-05-12 21:49:32'),(91,'App\\Models\\Quotation',6,'APPROVED_ONLINE','Quotation approved online by denica','customer','denica','127.0.0.1','online','{\"agreement\":true}','2026-05-12 21:49:37'),(92,'App\\Models\\Quotation',6,'DEPOSIT_PENDING','Deposit of KES 12,500.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-12 21:49:37'),(93,'App\\Models\\QuoteRequest',5,'REQUEST_SUBMITTED','Quote request created by admin','admin','Cyrus Kirop','127.0.0.1','system','[]','2026-05-12 22:00:22'),(94,'App\\Models\\Quotation',7,'QUOTE_CREATED','Quotation created by admin','admin','Cyrus Kirop',NULL,'system','[]','2026-05-12 22:00:51'),(95,'App\\Models\\Quotation',7,'QUOTE_SENT','Quotation sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-12 22:01:03'),(96,'App\\Models\\Quotation',7,'APPROVAL_LINK_CLICKED','Customer clicked the approval link','customer','denica','127.0.0.1','online','[]','2026-05-12 22:01:23'),(97,'App\\Models\\Quotation',7,'APPROVED_ONLINE','Quotation approved online by denica','customer','denica','127.0.0.1','online','{\"agreement\":true}','2026-05-12 22:01:28'),(98,'App\\Models\\Quotation',7,'DEPOSIT_PENDING','Deposit of KES 3,200.00 required to confirm booking','system',NULL,NULL,NULL,'[]','2026-05-12 22:01:28'),(99,'App\\Models\\Quotation',7,'SERVICE_AGREEMENT_GENERATED','Service Agreement PDF generated','system','Cyrus Kirop',NULL,'pdf','{\"path\":\"service-agreements\\/service_agreement_5_20260513010830.pdf\"}','2026-05-12 22:08:31'),(100,'App\\Models\\Invoice',8,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-13 00:33:15'),(101,'App\\Models\\Invoice',8,'INVOICE_SENT','Invoice sent via email','admin','Cyrus Kirop',NULL,'email','[]','2026-05-13 00:33:29'),(102,'App\\Models\\Quotation',7,'SERVICE_AGREEMENT_GENERATED','Service Agreement PDF generated','system','Cyrus Kirop',NULL,'pdf','{\"storage_key\":\"agreements\\/service_agreement_5_20260513033444_1778632484_nboea2.pdf\"}','2026-05-13 00:34:50');
/*!40000 ALTER TABLE `booking_stages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `career_jobs`
--

DROP TABLE IF EXISTS `career_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `career_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `employment_type` varchar(255) DEFAULT NULL,
  `salary_range` varchar(255) DEFAULT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `requirements` longtext DEFAULT NULL,
  `status` enum('draft','open','closed') NOT NULL DEFAULT 'draft',
  `posted_at` timestamp NULL DEFAULT NULL,
  `closes_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `career_jobs_slug_unique` (`slug`),
  KEY `career_jobs_status_index` (`status`),
  KEY `career_jobs_posted_at_index` (`posted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `career_jobs`
--

LOCK TABLES `career_jobs` WRITE;
/*!40000 ALTER TABLE `career_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `career_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contact_key` varchar(255) NOT NULL,
  `source_quote_request_id` bigint(20) unsigned DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `moving_from` varchar(255) DEFAULT NULL,
  `moving_to` varchar(255) DEFAULT NULL,
  `latest_service_type` varchar(255) DEFAULT NULL,
  `quotes_count` int(11) NOT NULL DEFAULT 0,
  `approved_quotes_count` int(11) NOT NULL DEFAULT 0,
  `declined_quotes_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('lead','active_client','completed','inactive') NOT NULL DEFAULT 'lead',
  `first_seen_at` timestamp NULL DEFAULT NULL,
  `last_quote_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_contact_key_unique` (`contact_key`),
  KEY `customers_email_index` (`email`),
  KEY `customers_phone_index` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'customer10@example.com|0700000010',NULL,'C1','customer10@example.com','0700000010',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(2,'customer9@example.com|0700000009',NULL,'C9','customer9@example.com','0700000009',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(3,'customer8@example.com|0700000008',NULL,'C8','customer8@example.com','0700000008',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(6,'customer5@example.com|0700000005',NULL,'C5','customer5@example.com','0700000005',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(7,'customer4@example.com|0700000004',NULL,'C4','customer4@example.com','0700000004',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(8,'customer3@example.com|0700000003',NULL,'C3','customer3@example.com','0700000003',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(9,'customer2@example.com|0700000002',NULL,'C2','customer2@example.com','0700000002',NULL,NULL,NULL,0,0,0,'lead','2026-04-30 21:00:00','2026-04-30 21:00:00','2026-05-10 07:33:17','2026-05-10 07:33:17'),(11,'kiropcyrus028@gmail.com|0722880726',NULL,'cyrus kipruto kirop','kiropcyrus028@gmail.com','0722880726',NULL,NULL,NULL,0,0,0,'lead','2026-05-10 08:57:24',NULL,'2026-05-10 08:57:24','2026-05-10 08:57:24'),(12,'kiropcyrus028@gmail.com|0769685995',1,'cyrus kipruto kirop','kiropcyrus028@gmail.com','0769685995','karen','ngong','Residential Relocation',1,1,0,'active_client','2026-05-10 09:00:43','2026-05-10 09:00:43','2026-05-10 09:05:11','2026-05-12 23:55:21'),(25,'hydrasoftke@gmail.com|0112587581',2,'roy okoth','hydrasoftke@gmail.com','0112587581','londiani rd','kirinyaga rd','Residential Relocation',1,1,0,'completed','2026-05-10 10:48:27','2026-05-10 10:48:27','2026-05-10 10:53:54','2026-05-12 23:55:21'),(206,'fricadenica@gmail.com|0712345678',5,'denica','fricadenica@gmail.com','0712345678','Londiani ward, Kipkelion East, Kericho County, Kenya','Junction Maili Mbili, Hells Gate ward, Naivasha, Nakuru, Kenya','Residential Relocation',2,2,0,'active_client','2026-05-12 21:48:37','2026-05-12 22:00:22','2026-05-12 23:32:06','2026-05-12 23:55:21');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dashboard_monthly_metrics`
--

DROP TABLE IF EXISTS `dashboard_monthly_metrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboard_monthly_metrics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `month` date NOT NULL,
  `completed_moves` int(10) unsigned NOT NULL DEFAULT 0,
  `cancelled_bookings` int(10) unsigned NOT NULL DEFAULT 0,
  `desktop_visitors` int(10) unsigned NOT NULL DEFAULT 0,
  `mobile_visitors` int(10) unsigned NOT NULL DEFAULT 0,
  `tablet_visitors` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dashboard_monthly_metrics_month_unique` (`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_monthly_metrics`
--

LOCK TABLES `dashboard_monthly_metrics` WRITE;
/*!40000 ALTER TABLE `dashboard_monthly_metrics` DISABLE KEYS */;
/*!40000 ALTER TABLE `dashboard_monthly_metrics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_delivery_logs`
--

DROP TABLE IF EXISTS `email_delivery_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_delivery_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `form_type` varchar(255) NOT NULL DEFAULT 'contact',
  `recipient_email` varchar(190) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `direction` varchar(50) NOT NULL DEFAULT 'client',
  `subject` varchar(190) DEFAULT NULL,
  `transport` varchar(50) NOT NULL DEFAULT 'smtp',
  `response_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_delivery_logs_form_type_index` (`form_type`),
  KEY `email_delivery_logs_recipient_email_index` (`recipient_email`),
  KEY `email_delivery_logs_status_index` (`status`),
  KEY `email_delivery_logs_direction_index` (`direction`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_delivery_logs`
--

LOCK TABLES `email_delivery_logs` WRITE;
/*!40000 ALTER TABLE `email_delivery_logs` DISABLE KEYS */;
INSERT INTO `email_delivery_logs` VALUES (4,'invoice','hydrasoftke@gmail.com','sent','client','Invoice INV-00002 from KwikShift Movers & Relocators','smtp','Invoice email accepted by the mail transport. Message ID: 42820737a97b57ed0bcf628aeeedf017@fumiklincleaners.com','2026-05-10 11:08:33','2026-05-10 11:08:33'),(5,'quotation','hydrasoftke@gmail.com','sent','client','Quotation #QT00002 from KwikShift Movers & Relocators','smtp','Quotation email accepted by the mail transport. Message ID: f5de70b351023e259dd3a59985e97819@fumiklincleaners.com','2026-05-10 14:18:50','2026-05-10 14:18:50'),(6,'invoice','hydrasoftke@gmail.com','sent','client','Invoice INV-00001 from KwikShift Movers & Relocators','smtp','Invoice email accepted by the mail transport. Message ID: a4a68dc5d48df2d8190b41450c66ac38@fumiklincleaners.com','2026-05-10 14:22:41','2026-05-10 14:22:41'),(7,'invoice','kiropcyrus028@gmail.com','sent','client','Invoice INV-00004 from KwikShift Movers & Relocators','smtp','Invoice email accepted by the mail transport. Message ID: 67421b1962ab824e9fbd87bbdaa9573c@fumiklincleaners.com','2026-05-10 14:30:31','2026-05-10 14:30:31'),(8,'quotation','hydrasoftke@gmail.com','failed','client','Quotation #QT00002 from KwikShift Movers & Relocators','smtp','Email failed: Connection could not be established with host \"smtp-relay.brevo.com:587\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp-relay.brevo.com failed: Temporary failure in name resolution','2026-05-10 15:21:37','2026-05-10 15:21:37'),(9,'quotation','hydrasoftke@gmail.com','failed','client','Quotation #QT00002 from KwikShift Movers & Relocators','smtp','Email failed: Connection could not be established with host \"smtp-relay.brevo.com:587\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp-relay.brevo.com failed: Temporary failure in name resolution','2026-05-10 15:22:42','2026-05-10 15:22:42'),(10,'quotation','hydrasoftke@gmail.com','sent','client','Quotation #QT00002 from KwikShift Movers & Relocators','smtp','Quotation email accepted by the mail transport. Message ID: e328b449a0fa306dffb0b58b27a46e88@fumiklincleaners.com','2026-05-10 15:25:04','2026-05-10 15:25:04'),(11,'invoice','hydrasoftke@gmail.com','failed','client','Invoice INV-00005 from KwikShift Movers & Relocators','smtp','The PHP GD extension is required, but is not installed.','2026-05-10 15:38:39','2026-05-10 15:38:39'),(12,'invoice','hydrasoftke@gmail.com','failed','client','Invoice INV-00005 from KwikShift Movers & Relocators','smtp','The PHP GD extension is required, but is not installed.','2026-05-10 15:38:45','2026-05-10 15:38:45'),(13,'invoice','hydrasoftke@gmail.com','failed','client','Invoice INV-00005 from KwikShift Movers & Relocators','smtp','The PHP GD extension is required, but is not installed.','2026-05-10 15:43:59','2026-05-10 15:43:59'),(14,'invoice','hydrasoftke@gmail.com','sent','client','Invoice INV-00005 from KwikShift Movers & Relocators','smtp','Invoice email accepted by the mail transport. Message ID: 9a77f6bbd616e6358f03e006ec7498c0@fumiklincleaners.com','2026-05-10 16:21:23','2026-05-10 16:21:23'),(15,'quotation','kiprutocyrus999@gmail.com','sent','client','Quotation #QT00003 from KwikShift Movers & Relocators','smtp','Quotation email accepted by the mail transport. Message ID: f58c9d555065cbf7b164951723ee0f56@fumiklincleaners.com','2026-05-11 13:38:13','2026-05-11 13:38:13'),(16,'invoice','kiprutocyrus999@gmail.com','sent','client','Invoice INV-00006 from KwikShift Movers & Relocators','smtp','Invoice email accepted by the mail transport. Message ID: c2d179c48e099685b578c00cdcc4f499@fumiklincleaners.com','2026-05-11 14:05:32','2026-05-11 14:05:32'),(17,'message','hydrasoftke@gmail.com','sent','client','follow up on our quotation','smtp','Message email sent successfully.','2026-05-11 21:49:20','2026-05-11 21:49:20'),(18,'invoice','hydrasoftke@gmail.com','sent','client','Invoice INV-00004 from KwikShift Movers & Relocators','smtp','Invoice email accepted by the mail transport. Message ID: 1a769dde7a03207f6f803e85beb14097@fumiklincleaners.com','2026-05-11 22:08:47','2026-05-11 22:08:47'),(19,'quotation','hydrasoftke@gmail.com','sent','client','Quotation #QT00002 from KwikShift Movers & Relocators','smtp','Quotation email accepted by the mail transport. Message ID: 10b89b769e6759dba9171590ddf353a0@fumiklincleaners.com','2026-05-11 22:21:13','2026-05-11 22:21:13');
/*!40000 ALTER TABLE `email_delivery_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_logs`
--

DROP TABLE IF EXISTS `email_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `emailable_type` varchar(255) DEFAULT NULL,
  `emailable_id` bigint(20) unsigned DEFAULT NULL,
  `sender_role` varchar(40) DEFAULT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'queued',
  `tracking_token` char(36) NOT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `failed_reason` text DEFAULT NULL,
  `attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_logs_tracking_token_unique` (`tracking_token`),
  KEY `email_logs_emailable_type_emailable_id_index` (`emailable_type`,`emailable_id`),
  KEY `email_logs_recipient_email_index` (`recipient_email`),
  KEY `email_logs_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_logs`
--

LOCK TABLES `email_logs` WRITE;
/*!40000 ALTER TABLE `email_logs` DISABLE KEYS */;
INSERT INTO `email_logs` VALUES (1,NULL,NULL,NULL,NULL,NULL,'test@example.com','Test email - Kwikshift Admin Panel','sent','bd166384-55b1-42f4-b1e1-c55a08294184','2026-05-09 15:50:51',NULL,NULL,1,'2026-05-09 15:50:51','2026-05-09 15:50:51'),(2,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','d56098d9-8881-4b46-af52-35fd65cf96a7','2026-05-09 15:51:41',NULL,NULL,1,'2026-05-09 15:51:28','2026-05-09 15:51:41'),(3,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','a7a2e93a-791d-4518-9125-91ab146d9fd3','2026-05-09 15:52:08',NULL,NULL,1,'2026-05-09 15:51:51','2026-05-09 15:52:08'),(4,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','564a3782-58ea-414c-a9f3-6a95a0e0a1c1','2026-05-10 06:25:36',NULL,NULL,1,'2026-05-10 06:25:32','2026-05-10 06:25:36'),(5,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','4f977639-9b8e-4a30-888e-14849071c58b','2026-05-10 06:28:21',NULL,NULL,1,'2026-05-10 06:28:18','2026-05-10 06:28:21'),(6,NULL,NULL,NULL,NULL,NULL,'kiropcyrus028@gmail.com','This is Test mail','sent','08899825-1748-4035-8a7c-5557747fa568','2026-05-10 06:32:47',NULL,NULL,1,'2026-05-10 06:32:41','2026-05-10 06:32:47'),(7,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','e86c62a6-bf39-4a1c-bbf8-c3f660d39428','2026-05-10 06:38:55',NULL,NULL,1,'2026-05-10 06:38:52','2026-05-10 06:38:55'),(8,NULL,NULL,NULL,NULL,NULL,'hydrasoftke@gmail.com','sample test','sent','98212ef0-f601-4482-a25d-8890a9005817','2026-05-10 07:08:04',NULL,NULL,1,'2026-05-10 07:08:01','2026-05-10 07:08:04'),(9,NULL,NULL,NULL,NULL,NULL,'hydrasoftke@gmail.com','demo email','sent','80aff29e-9797-4b19-b406-1ed8684fdfc9','2026-05-10 07:31:25',NULL,NULL,1,'2026-05-10 07:31:22','2026-05-10 07:31:25'),(10,'App\\Models\\Quotation',1,NULL,NULL,NULL,'kiropcyrus028@gmail.com','Quotation #QT00001 from KwikShift Movers & Relocators','queued','05346ab1-b78f-40a5-97b3-3de87f627aef',NULL,NULL,NULL,0,'2026-05-10 09:05:05','2026-05-10 09:05:05'),(11,'App\\Models\\Quotation',1,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quotation approved #QT00001','queued','7bcdb2be-c075-4536-a56f-06583514541f',NULL,NULL,NULL,0,'2026-05-10 10:20:46','2026-05-10 10:20:46'),(12,'App\\Models\\Quotation',1,NULL,NULL,NULL,'kiropcyrus028@gmail.com','Your quotation is approved #QT00001','queued','68ecf7f0-b366-4aaf-85f9-e32b22de944c',NULL,NULL,NULL,0,'2026-05-10 10:20:49','2026-05-10 10:20:49'),(13,'App\\Models\\Invoice',1,NULL,NULL,NULL,'kiropcyrus028@gmail.com','Invoice INV-00001 from KwikShift Movers & Relocators','queued','b4fa1789-62d6-4207-b6cf-9d67eef8c380',NULL,NULL,NULL,0,'2026-05-10 10:21:50','2026-05-10 10:21:50'),(14,'App\\Models\\Invoice',1,NULL,NULL,NULL,'kiropcyrus028@gmail.com','Payment received for invoice INV-00001','sending','bee2a81b-7647-4893-aa3e-f6899dde2fe3',NULL,NULL,NULL,0,'2026-05-10 10:35:12','2026-05-10 10:35:12'),(15,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','38c6dc77-84cf-483a-9e2d-afe74c7e0ca2','2026-05-10 10:53:31',NULL,NULL,1,'2026-05-10 10:53:27','2026-05-10 10:53:31'),(16,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','e531ea6e-b389-4435-8b96-14f4f92bde9c','2026-05-10 10:53:54',NULL,NULL,1,'2026-05-10 10:53:51','2026-05-10 10:53:54'),(17,'App\\Models\\Quotation',2,NULL,NULL,NULL,'hydrasoftke@gmail.com','Quotation #QT00002 from KwikShift Movers & Relocators','sending','fc2382a7-4911-4638-b830-0393eb0ffd3b',NULL,NULL,NULL,0,'2026-05-10 10:56:43','2026-05-10 10:56:43'),(18,'App\\Models\\Quotation',2,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quotation approved #QT00002','sending','338c319f-72ed-4e96-a700-329716e26998',NULL,NULL,NULL,0,'2026-05-10 11:03:35','2026-05-10 11:03:35'),(19,'App\\Models\\Quotation',2,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your quotation is approved #QT00002','sending','5679aed1-1b74-4c3e-83ec-9e406ccee6d6',NULL,NULL,NULL,0,'2026-05-10 11:03:37','2026-05-10 11:03:37'),(20,'App\\Models\\Quotation',2,NULL,NULL,NULL,'hydrasoftke@gmail.com','Deposit received - booking confirmed #QT00002','sending','ccc236c1-8705-4cf3-9403-362a2b18e013',NULL,NULL,NULL,0,'2026-05-10 11:05:32','2026-05-10 11:05:32'),(21,'App\\Models\\Invoice',2,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00002 from KwikShift Movers & Relocators','sent','426ae8fe-a39c-4247-a277-dda3b99de2b2','2026-05-10 11:08:33',NULL,NULL,1,'2026-05-10 11:08:26','2026-05-10 11:08:33'),(22,'App\\Models\\Invoice',2,NULL,NULL,NULL,'hydrasoftke@gmail.com','Payment received for invoice INV-00002','sending','597ada71-8acc-4d4d-ac77-5e5f9450a45b',NULL,NULL,NULL,0,'2026-05-10 14:13:06','2026-05-10 14:13:06'),(23,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Quotation #QT00002 from KwikShift Movers & Relocators','sent','1c514d09-0427-4963-be87-0cbd9794218a','2026-05-10 14:18:50',NULL,NULL,1,'2026-05-10 14:18:43','2026-05-10 14:18:50'),(24,'App\\Models\\Quotation',3,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quotation approved #QT00002','sending','79dc0f06-f86b-47a5-bcd6-ef6167fa5237',NULL,NULL,NULL,0,'2026-05-10 14:20:43','2026-05-10 14:20:43'),(25,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your quotation is approved #QT00002','sending','efdc61f8-8a3a-417a-96fe-2289c9412225',NULL,NULL,NULL,0,'2026-05-10 14:20:46','2026-05-10 14:20:46'),(26,'App\\Models\\Invoice',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00001 from KwikShift Movers & Relocators','sent','13cd13ab-659b-49b1-9dc8-f673d86feeda','2026-05-10 14:22:41',NULL,NULL,1,'2026-05-10 14:22:35','2026-05-10 14:22:41'),(27,'App\\Models\\Invoice',4,NULL,NULL,NULL,'kiropcyrus028@gmail.com','Invoice INV-00004 from KwikShift Movers & Relocators','sent','9fd3c2d1-e323-47aa-a1f0-3b91c3c1a74f','2026-05-10 14:30:31',NULL,NULL,1,'2026-05-10 14:30:24','2026-05-10 14:30:31'),(28,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Quotation #QT00002 from KwikShift Movers & Relocators','failed','bda544cb-973c-4477-9b34-4c10e34605bb',NULL,NULL,'Connection could not be established with host \"smtp-relay.brevo.com:587\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp-relay.brevo.com failed: Temporary failure in name resolution',1,'2026-05-10 15:21:15','2026-05-10 15:21:37'),(29,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Quotation #QT00002 from KwikShift Movers & Relocators','failed','30fae7c9-b708-45b7-beff-5e18cb782a18',NULL,NULL,'Connection could not be established with host \"smtp-relay.brevo.com:587\": stream_socket_client(): php_network_getaddresses: getaddrinfo for smtp-relay.brevo.com failed: Temporary failure in name resolution',1,'2026-05-10 15:22:22','2026-05-10 15:22:42'),(30,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Quotation #QT00002 from KwikShift Movers & Relocators','sent','c5ce401f-b0a8-4b85-9cf5-a071fac5c4a2','2026-05-10 15:25:04',NULL,NULL,1,'2026-05-10 15:24:58','2026-05-10 15:25:04'),(31,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','aece2b75-3567-4570-a206-45190d2ce357','2026-05-10 15:37:17',NULL,NULL,1,'2026-05-10 15:37:15','2026-05-10 15:37:17'),(32,'App\\Models\\Invoice',5,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00005 from KwikShift Movers & Relocators','failed','37cc2e82-4e47-4b9a-94c8-c7e547a2bfc9',NULL,NULL,'The PHP GD extension is required, but is not installed.',1,'2026-05-10 15:38:38','2026-05-10 15:38:39'),(33,'App\\Models\\Invoice',5,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00005 from KwikShift Movers & Relocators','failed','cc45dc78-7734-4ddf-bfc9-ad84d6b54af3',NULL,NULL,'The PHP GD extension is required, but is not installed.',1,'2026-05-10 15:38:44','2026-05-10 15:38:45'),(34,'App\\Models\\Invoice',5,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00005 from KwikShift Movers & Relocators','failed','de2249ad-9a86-437c-912e-0a353026189e',NULL,NULL,'The PHP GD extension is required, but is not installed.',1,'2026-05-10 15:43:58','2026-05-10 15:43:58'),(35,'App\\Models\\Invoice',5,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00005 from KwikShift Movers & Relocators','sent','d8385594-6da7-421e-973a-8f6fda89c44b','2026-05-10 16:21:23',NULL,NULL,1,'2026-05-10 16:21:18','2026-05-10 16:21:23'),(36,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','de72f5e3-39ec-48e2-a266-3531ebd15cae','2026-05-11 11:12:45',NULL,NULL,1,'2026-05-11 11:12:35','2026-05-11 11:12:45'),(37,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','b0f8b0f1-8cce-4f7f-a80d-5a8ac6ccc058','2026-05-11 12:57:00',NULL,NULL,1,'2026-05-11 12:56:56','2026-05-11 12:57:00'),(38,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','51b3ad9c-051b-4314-b698-2604c6b18f69','2026-05-11 13:04:18',NULL,NULL,1,'2026-05-11 13:04:15','2026-05-11 13:04:18'),(39,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','997206b8-8f15-47c3-a92c-d1d058f56ec5','2026-05-11 13:05:11',NULL,NULL,1,'2026-05-11 13:05:08','2026-05-11 13:05:11'),(40,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','15458e01-e5b6-4eff-9b43-a6464735a6f7','2026-05-11 13:08:00',NULL,NULL,1,'2026-05-11 13:07:53','2026-05-11 13:08:00'),(41,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','cae97744-c3a3-4df3-8341-0b55ea061be7','2026-05-11 13:08:32',NULL,NULL,1,'2026-05-11 13:08:29','2026-05-11 13:08:32'),(42,'App\\Models\\Quotation',5,NULL,NULL,NULL,'kiprutocyrus999@gmail.com','Quotation #QT00003 from KwikShift Movers & Relocators','sent','897bfb5a-5732-47ae-a22e-eae5dced60ea','2026-05-11 13:38:13',NULL,NULL,1,'2026-05-11 13:38:08','2026-05-11 13:38:13'),(43,'App\\Models\\Quotation',5,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quotation approved #QT00003','sending','f3ce48bf-be6e-436c-b056-fd513881a824',NULL,NULL,NULL,0,'2026-05-11 13:56:59','2026-05-11 13:56:59'),(44,'App\\Models\\Quotation',5,NULL,NULL,NULL,'kiprutocyrus999@gmail.com','Your quotation is approved #QT00003','sending','f74a45be-7362-48d9-bfc8-de9b89c869e7',NULL,NULL,NULL,0,'2026-05-11 13:57:01','2026-05-11 13:57:01'),(45,'App\\Models\\Quotation',5,NULL,NULL,NULL,'kiprutocyrus999@gmail.com','Deposit received - booking confirmed #QT00003','sending','07fd6140-7c80-4f4b-9794-b99250f21288',NULL,NULL,NULL,0,'2026-05-11 13:59:47','2026-05-11 13:59:47'),(46,'App\\Models\\Invoice',6,NULL,NULL,NULL,'kiprutocyrus999@gmail.com','Invoice INV-00006 from KwikShift Movers & Relocators','sent','af628f57-0b6f-4a07-a4b2-ba12a0a205d9','2026-05-11 14:05:32',NULL,NULL,1,'2026-05-11 14:05:27','2026-05-11 14:05:32'),(47,'App\\Models\\Invoice',6,NULL,NULL,NULL,'kiprutocyrus999@gmail.com','Payment received for invoice INV-00006','sending','d295afee-6cdf-4598-bc77-0c9db0c75242',NULL,NULL,NULL,0,'2026-05-11 14:08:25','2026-05-11 14:08:25'),(48,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','71bb00fc-3e39-4095-8501-95df1b905f1d','2026-05-11 18:55:11',NULL,NULL,1,'2026-05-11 18:55:04','2026-05-11 18:55:11'),(49,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','8d1432a5-dfda-4e0e-a994-0d6465852c46','2026-05-11 19:04:39',NULL,NULL,1,'2026-05-11 19:04:27','2026-05-11 19:04:39'),(50,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','e2aa508a-5b94-4d00-b392-186640d21931','2026-05-11 19:05:48',NULL,NULL,1,'2026-05-11 19:05:45','2026-05-11 19:05:48'),(51,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Admin Panel','sent','0846f929-e489-406e-bf96-c5c72332709c','2026-05-11 21:46:49',NULL,NULL,1,'2026-05-11 21:46:44','2026-05-11 21:46:49'),(52,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Admin Panel','sent','8f911c63-4195-420f-88c4-cc9b3e831345','2026-05-11 21:47:56',NULL,NULL,1,'2026-05-11 21:47:54','2026-05-11 21:47:56'),(53,NULL,NULL,'info','info@fumiklincleaners.com','Kwikshift Movers','hydrasoftke@gmail.com','follow up on our quotation','sent','f3a7aa4b-3b33-4ae7-9dd2-1d05cb820270','2026-05-11 21:49:20',NULL,NULL,1,'2026-05-11 21:49:17','2026-05-11 21:49:20'),(54,'App\\Models\\Invoice',4,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00004 from KwikShift Movers & Relocators','sent','62b2ba9e-1e0e-487a-b4ba-0b919989b0ee','2026-05-11 22:08:47',NULL,NULL,1,'2026-05-11 22:08:42','2026-05-11 22:08:47'),(55,'App\\Models\\Invoice',4,NULL,NULL,NULL,'kiropcyrus028@gmail.com','Payment received for invoice INV-00004','sending','64ab9587-de65-46a3-a258-ca013d19077b',NULL,NULL,NULL,0,'2026-05-11 22:10:50','2026-05-11 22:10:50'),(56,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Quotation #QT00002 from KwikShift Movers & Relocators','sent','2067ea8e-abf2-4aff-85b8-b7cc49b93bf2','2026-05-11 22:21:12',NULL,NULL,1,'2026-05-11 22:21:08','2026-05-11 22:21:12'),(57,'App\\Models\\Quotation',3,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quotation approved #QT00002','sending','57c3712b-9221-4d31-ad17-a7aadf46ee1f',NULL,NULL,NULL,0,'2026-05-11 22:22:41','2026-05-11 22:22:41'),(58,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your quotation is approved #QT00002','sending','3b7320b0-f51e-48d6-a97d-e33bd65a8093',NULL,NULL,NULL,0,'2026-05-11 22:22:43','2026-05-11 22:22:43'),(59,'App\\Models\\Quotation',3,NULL,NULL,NULL,'hydrasoftke@gmail.com','Deposit received - booking confirmed #QT00002','sending','d246af46-039e-4ab7-9628-a1699173362d',NULL,NULL,NULL,0,'2026-05-11 22:43:53','2026-05-11 22:43:53'),(60,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','ce758687-2329-4d71-b15d-040afb7bf797','2026-05-11 22:44:40',NULL,NULL,1,'2026-05-11 22:44:37','2026-05-11 22:44:40'),(61,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','300b722d-8734-4be7-bafc-8d6db7a5d1ab','2026-05-11 23:03:32',NULL,NULL,1,'2026-05-11 23:03:28','2026-05-11 23:03:32'),(62,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Movers','sent','93ffb135-8aa8-4ba7-8825-d335a4691473','2026-05-11 23:06:44',NULL,NULL,1,'2026-05-11 23:06:40','2026-05-11 23:06:44'),(63,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Movers','sent','973d6eeb-f050-46ac-9484-24907896aee2','2026-05-11 23:07:12',NULL,NULL,1,'2026-05-11 23:07:10','2026-05-11 23:07:12'),(64,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','ef02d613-1332-432c-aa92-fd2bd06b6e12','2026-05-11 23:07:35',NULL,NULL,1,'2026-05-11 23:07:32','2026-05-11 23:07:35'),(65,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','46148155-6c0e-497f-bb5b-464106f61893','2026-05-11 23:09:18',NULL,NULL,1,'2026-05-11 23:09:15','2026-05-11 23:09:18'),(66,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','c7c857aa-d9aa-4e59-a9d5-9d3a4b1075c0','2026-05-11 23:09:53',NULL,NULL,1,'2026-05-11 23:09:50','2026-05-11 23:09:53'),(67,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','4b6eda22-8296-4c29-83a4-d086dfcdd126','2026-05-11 23:12:57',NULL,NULL,1,'2026-05-11 23:12:54','2026-05-11 23:12:57'),(68,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','ac27bd9d-d77b-4b3e-9b0d-90170f782c12','2026-05-11 23:15:21',NULL,NULL,1,'2026-05-11 23:15:18','2026-05-11 23:15:21'),(69,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','e607a3ed-2268-471c-ac89-5585e34d06b5','2026-05-11 23:16:47',NULL,NULL,1,'2026-05-11 23:16:44','2026-05-11 23:16:47'),(70,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','d8856d13-6c1a-4585-9202-39a3e06ad99c','2026-05-11 23:17:55',NULL,NULL,1,'2026-05-11 23:17:52','2026-05-11 23:17:55'),(71,'App\\Models\\Invoice',7,NULL,NULL,NULL,'hydrasoftke@gmail.com','Payment received for invoice INV-00007','sent','c8932cb0-0319-417f-9d8f-099096b89186','2026-05-11 23:27:01',NULL,NULL,1,'2026-05-11 23:26:59','2026-05-11 23:27:01'),(72,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','Your verification code — Kwikshift Movers','sent','b71e761f-ae10-46b6-83a3-ad083ce4b0fb','2026-05-12 21:45:13',NULL,NULL,1,'2026-05-12 21:45:10','2026-05-12 21:45:13'),(73,'App\\Models\\User',1,NULL,NULL,NULL,'hydrasoftke@gmail.com','New login detected - Kwikshift Movers','sent','fbc97edb-72d8-4495-8d07-b64f501f81d5','2026-05-12 21:45:50',NULL,NULL,1,'2026-05-12 21:45:48','2026-05-12 21:45:50'),(74,'App\\Models\\Quotation',6,NULL,NULL,NULL,'fricadenica@gmail.com','Quotation #QT00004 from KwikShift Movers & Relocators','sent','957d37a9-0023-4fb2-b050-d8c3367cfe40','2026-05-12 21:49:16',NULL,NULL,1,'2026-05-12 21:49:12','2026-05-12 21:49:16'),(75,'App\\Models\\Quotation',6,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quote approved by denica','sent','042ef0ca-da43-4981-8199-94a111ac5be7','2026-05-12 21:49:40',NULL,NULL,1,'2026-05-12 21:49:38','2026-05-12 21:49:40'),(76,'App\\Models\\Quotation',6,NULL,NULL,NULL,'fricadenica@gmail.com','Your quotation is approved #QT00004','sent','9af78b6c-fac2-4931-ba61-692b5051d870','2026-05-12 21:49:41',NULL,NULL,1,'2026-05-12 21:49:40','2026-05-12 21:49:41'),(77,'App\\Models\\Quotation',7,NULL,NULL,NULL,'fricadenica@gmail.com','Quotation #QT00005 from KwikShift Movers & Relocators','sent','dcf76826-d417-4a7f-8468-0aa03a4cdf94','2026-05-12 22:01:03',NULL,NULL,1,'2026-05-12 22:00:58','2026-05-12 22:01:03'),(78,'App\\Models\\Quotation',7,NULL,NULL,NULL,'info@kwikshiftmovers.co.ke','Quote approved by denica','sent','6a658ef3-8f7c-43bb-a0e5-b92f6788dfab','2026-05-12 22:01:31',NULL,NULL,1,'2026-05-12 22:01:29','2026-05-12 22:01:31'),(79,'App\\Models\\Quotation',7,NULL,NULL,NULL,'fricadenica@gmail.com','Your quotation is approved #QT00005','sent','676a1a51-bca4-40ec-a2bd-ac8e6bd3888a','2026-05-12 22:01:32',NULL,NULL,1,'2026-05-12 22:01:31','2026-05-12 22:01:32'),(80,'App\\Models\\Invoice',8,NULL,NULL,NULL,'fricadenica@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','61ebbbd6-095c-4139-a497-c66fe9d1c1c8','2026-05-13 00:33:16',NULL,NULL,1,'2026-05-13 00:33:06','2026-05-13 00:33:16'),(81,'App\\Models\\Invoice',8,NULL,NULL,NULL,'hydrasoftke@gmail.com','Invoice INV-00007 from KwikShift Movers & Relocators','sent','a9ccb576-7e1d-4212-a89c-89f1f1974fe0','2026-05-13 00:33:29',NULL,NULL,1,'2026-05-13 00:33:21','2026-05-13 00:33:29');
/*!40000 ALTER TABLE `email_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(80) NOT NULL DEFAULT 'general',
  `display_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_status_category_display_order_index` (`status`,`category`,`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gallery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_public_id` varchar(500) DEFAULT NULL,
  `legacy_image_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_category_index` (`category`),
  KEY `gallery_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery`
--

LOCK TABLES `gallery` WRITE;
/*!40000 ALTER TABLE `gallery` DISABLE KEYS */;
/*!40000 ALTER TABLE `gallery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_index` (`invoice_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (10,4,'Residential Relocation - karen to ngong - #QT00001',1,25000.00,25000.00,NULL,NULL),(14,6,'Transportation & Fuel - Moving vehicle rental and fuel',1,8333.34,8333.34,NULL,NULL),(15,6,'Labour Charges - Professional moving team (2-3 persons)',1,8333.33,8333.33,NULL,NULL),(16,6,'Loading & Unloading - Professional loading and unloading service',1,8333.33,8333.33,NULL,NULL),(20,8,'Transportation & Fuel - Moving vehicle rental and fuel',1,10666.67,10666.67,NULL,NULL),(21,8,'Labour Charges - Professional moving team (2-3 persons)',1,10666.67,10666.67,NULL,NULL),(22,8,'Loading & Unloading - Professional loading and unloading service',1,10666.66,10666.66,NULL,NULL);
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) NOT NULL,
  `quote_request_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `move_origin` varchar(255) DEFAULT NULL,
  `move_destination` varchar(255) DEFAULT NULL,
  `move_date` date DEFAULT NULL,
  `move_size` varchar(255) DEFAULT NULL,
  `quote_reference` varchar(255) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('paid','unpaid','pending','draft','failed','sent','overdue','void','cancelled') NOT NULL DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `sent_via` varchar(255) DEFAULT NULL,
  `sent_to_email` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pdf_storage_key` varchar(500) DEFAULT NULL,
  `pdf_storage_file_id` varchar(200) DEFAULT NULL,
  `pdf_storage_url` varchar(1000) DEFAULT NULL,
  `legacy_pdf_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_invoice_number_index` (`invoice_number`),
  KEY `invoices_quote_request_id_index` (`quote_request_id`),
  KEY `invoices_status_index` (`status`),
  CONSTRAINT `invoices_quote_request_id_foreign` FOREIGN KEY (`quote_request_id`) REFERENCES `quote_requests` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (4,'INV-00004',1,'cyrus kipruto kirop','kiropcyrus028@gmail.com','0769685995','karen','ngong','2026-05-15','1 Bedroom','#QT00001','2026-05-10','2026-05-17',25000.00,0.00,25000.00,'paid','2026-05-11 22:08:47','email','hydrasoftke@gmail.com','2026-05-11 22:10:50','mobile_money',NULL,'2026-05-10 14:30:24','2026-05-11 22:10:50',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'INV-00006',NULL,'john doe','kiprutocyrus999@gmail.com','0722880726','Kasarani, Nairobi, Nairobi County, Kenya','Uthiru, Waithaka division, Dagoretti, Nairobi, Nairobi County, 29039, Kenya','2026-05-15','1500sq','#QT00003','2026-05-11','2026-05-18',25000.00,0.00,25000.00,'paid','2026-05-11 14:05:32','email','kiprutocyrus999@gmail.com','2026-05-11 14:08:24','mobile_money',NULL,'2026-05-11 13:58:35','2026-05-11 14:08:24',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,'INV-00007',5,'denica','fricadenica@gmail.com','0712345678','Londiani ward, Kipkelion East, Kericho County, Kenya','Junction Maili Mbili, Hells Gate ward, Naivasha, Nakuru, Kenya','2026-05-15','1 Bedroom','#QT00005','2026-05-13','2026-05-20',32000.00,0.00,32000.00,'sent','2026-05-13 00:33:29','email','hydrasoftke@gmail.com',NULL,'mobile_money',NULL,'2026-05-13 00:33:00','2026-05-13 00:33:29','invoices/invoice-inv-00007-denica-1996b754-79cd-43c6-8ddf-7531fa0fbd04_1778632401_zplkzq.pdf','4_z564b1941714b13ea96e90417_f11338a834cc56d33_d20260513_m003325_c005_v0501041_t0006_u01778632405079','https://f005.backblazeb2.com/file/kwikshift/invoices/invoice-inv-00007-denica-1996b754-79cd-43c6-8ddf-7531fa0fbd04_1778632401_zplkzq.pdf?Authorization=3_20260513003327_acc1310b48515680e2714d75_0d190fabf0b14aa19e61c43825d314d52868920f_005_20260513013327_0092_dnld',NULL,'invoices/invoice-inv-00007-denica-1996b754-79cd-43c6-8ddf-7531fa0fbd04_1778632401_zplkzq.pdf','https://f005.backblazeb2.com/file/kwikshift/invoices/invoice-inv-00007-denica-1996b754-79cd-43c6-8ddf-7531fa0fbd04_1778632401_zplkzq.pdf?Authorization=3_20260513003327_acc1310b48515680e2714d75_0d190fabf0b14aa19e61c43825d314d52868920f_005_20260513013327_0092_dnld',NULL);
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_applications`
--

DROP TABLE IF EXISTS `job_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `career_job_id` bigint(20) unsigned DEFAULT NULL,
  `job_title` varchar(255) NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `current_location` varchar(255) DEFAULT NULL,
  `resume_url` varchar(255) DEFAULT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('new','reviewing','shortlisted','rejected','hired') NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `applied_at` timestamp NULL DEFAULT NULL,
  `source_page` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pdf_storage_key` varchar(500) DEFAULT NULL,
  `pdf_storage_file_id` varchar(200) DEFAULT NULL,
  `pdf_storage_url` varchar(1000) DEFAULT NULL,
  `legacy_pdf_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_applications_career_job_id_index` (`career_job_id`),
  KEY `job_applications_status_index` (`status`),
  KEY `job_applications_email_index` (`email`),
  KEY `job_applications_applied_at_index` (`applied_at`),
  CONSTRAINT `job_applications_career_job_id_foreign` FOREIGN KEY (`career_job_id`) REFERENCES `career_jobs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_applications`
--

LOCK TABLES `job_applications` WRITE;
/*!40000 ALTER TABLE `job_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
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
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'emails','{\"uuid\":\"e2d1205b-8cf7-462c-874c-e9de4a878703\",\"displayName\":\"App\\\\Jobs\\\\SendQuotationEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendQuotationEmailJob\",\"command\":\"O:30:\\\"App\\\\Jobs\\\\SendQuotationEmailJob\\\":8:{s:43:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000quotationId\\\";i:1;s:46:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000recipientEmail\\\";s:23:\\\"kiropcyrus028@gmail.com\\\";s:39:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000subject\\\";s:53:\\\"Quotation #QT00001 from KwikShift Movers & Relocators\\\";s:39:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000message\\\";s:504:\\\"Dear cyrus kipruto kirop,\\r\\n\\r\\nPlease find attached your quotation #QT00001 from KwikShift Movers & Relocators.\\r\\n\\r\\nQuotation Summary:\\r\\n- Quote Number: #QT00001\\r\\n- Date: 10 May 2026\\r\\n- Valid Until: 17 May 2026\\r\\n- Total Amount: KES 25,400.00\\r\\n\\r\\nThis quotation is valid for 7 days from the date of issue. Please do not hesitate to contact us if you have any questions.\\r\\n\\r\\nThank you for choosing KwikShift Movers & Relocators.\\r\\n\\r\\nBest regards,\\r\\nCyrus Kirop\\r\\nAuthorized Signatory\\r\\n+254 112587581 \\/ +254111330980\\\";s:41:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000attachPdf\\\";b:1;s:38:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000userId\\\";i:1;s:42:\\\"\\u0000App\\\\Jobs\\\\SendQuotationEmailJob\\u0000emailLogId\\\";i:10;s:5:\\\"queue\\\";s:6:\\\"emails\\\";}\",\"batchId\":null},\"createdAt\":1778414705,\"delay\":null}',0,NULL,1778414705,1778414705);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','responded','draft','sent') NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `responded_by` bigint(20) unsigned DEFAULT NULL,
  `origin_page` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_original_name` varchar(255) DEFAULT NULL,
  `attachment_mime` varchar(100) DEFAULT NULL,
  `email_log_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `image_url` varchar(1000) DEFAULT NULL,
  `image_public_id` varchar(500) DEFAULT NULL,
  `legacy_image_path` varchar(500) DEFAULT NULL,
  `pdf_storage_key` varchar(500) DEFAULT NULL,
  `pdf_storage_file_id` varchar(200) DEFAULT NULL,
  `pdf_storage_url` varchar(1000) DEFAULT NULL,
  `legacy_pdf_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_responded_by_foreign` (`responded_by`),
  KEY `messages_email_index` (`email`),
  KEY `messages_status_index` (`status`),
  KEY `messages_email_log_id_index` (`email_log_id`),
  CONSTRAINT `messages_responded_by_foreign` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (4,'Hydrasoftke','hydrasoftke@gmail.com',NULL,'follow up on our quotation','hello Cyrus ,\nwe are pleased to inform you about our leasing process and we we really fill loved to tell you about this','sent','2026-05-11 21:49:33',NULL,'2026-05-11 21:49:20',NULL,'compose',NULL,NULL,NULL,53,'2026-05-11 21:49:17','2026-05-11 23:29:23','2026-05-11 23:29:23',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_05_01_000001_create_customers_table',1),(5,'2024_05_01_000002_create_quote_requests_table',1),(6,'2024_05_01_000003_create_quotations_table',1),(7,'2024_05_01_000004_create_invoices_table',1),(8,'2024_05_01_000005_create_invoice_items_table',1),(9,'2024_05_01_000006_create_messages_table',1),(10,'2024_05_01_000007_create_gallery_table',1),(11,'2024_05_01_000008_create_dashboard_monthly_metrics_table',1),(12,'2024_05_01_000009_create_email_delivery_logs_table',1),(13,'2024_05_01_000010_normalize_email_delivery_logs_table',1),(14,'2024_05_01_000011_add_notes_to_invoices_table',1),(15,'2024_05_01_000012_update_invoice_statuses',1),(16,'2024_05_01_000013_normalize_legacy_email_delivery_log_columns',1),(17,'2024_05_01_000014_create_career_jobs_table',1),(18,'2024_05_01_000015_create_job_applications_table',1),(19,'2024_05_01_000016_create_reviews_table',1),(20,'2024_05_01_000017_create_faqs_table',1),(21,'2024_05_01_000018_normalize_gallery_content_columns',1),(22,'2026_05_07_000001_add_account_profile_fields_to_users_table',1),(23,'2026_05_07_000002_create_app_settings_table',1),(24,'2026_05_07_000003_create_todo_tasks_table',1),(25,'2026_05_08_000004_add_missing_gallery_admin_columns',1),(26,'2026_05_08_000005_add_created_status_to_quote_requests',1),(27,'2026_05_08_000006_add_account_signature_to_users_table',1),(28,'2026_05_08_000007_add_quote_authorization_profile_fields',1),(29,'2026_05_08_000008_add_invoice_delivery_fields_to_invoices_table',1),(30,'2026_05_08_000009_create_email_logs_table',1),(31,'2026_05_08_000010_update_invoice_workflow_statuses',1),(32,'2026_05_08_230218_update_message_status_enum',1),(33,'2026_05_09_000011_seed_company_app_settings',1),(34,'2026_05_09_000012_update_messages_email_delivery_fields',1),(35,'2026_05_09_000013_add_auth_security_fields_to_users_table',1),(36,'2026_05_10_000001_create_activity_notifications_table',2),(37,'2026_05_10_000002_add_sender_fields_to_email_logs_table',2),(38,'2026_05_10_000003_add_booking_flow_columns',2),(39,'2026_05_12_000001_add_completed_status_to_quote_requests',3),(40,'2026_05_13_000001_add_service_agreement_fields_to_quotations',4),(41,'2026_05_13_000002_add_b2_storage_columns',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `quote_request_id` bigint(20) unsigned NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_email` varchar(255) NOT NULL,
  `company_phone` varchar(255) NOT NULL,
  `company_website` varchar(255) DEFAULT NULL,
  `quote_date` date NOT NULL,
  `quote_valid_until` date DEFAULT NULL,
  `deposit_percentage` decimal(5,2) DEFAULT NULL,
  `cancellation_notice_hours` int(11) DEFAULT NULL,
  `cancellation_policy` text DEFAULT NULL,
  `services_included` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`services_included`)),
  `additional_notes` text DEFAULT NULL,
  `payment_terms` text DEFAULT NULL,
  `status` enum('draft','sent','approved','declined','expired') NOT NULL DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `sent_via` varchar(255) DEFAULT NULL,
  `deposit_amount` decimal(10,2) DEFAULT NULL,
  `deposit_paid` tinyint(1) NOT NULL DEFAULT 0,
  `deposit_paid_at` timestamp NULL DEFAULT NULL,
  `deposit_reference` varchar(255) DEFAULT NULL,
  `deposit_method` varchar(255) DEFAULT NULL,
  `deposit_whatsapp_url` text DEFAULT NULL,
  `reminder_whatsapp_url` text DEFAULT NULL,
  `followup_whatsapp_url` text DEFAULT NULL,
  `moving_from` varchar(255) DEFAULT NULL,
  `moving_to` varchar(255) DEFAULT NULL,
  `move_date` date DEFAULT NULL,
  `quote_amount` decimal(10,2) DEFAULT NULL,
  `authorized_by` varchar(255) DEFAULT NULL,
  `authorized_role` varchar(255) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `approval_token` varchar(255) DEFAULT NULL,
  `approval_token_expires_at` timestamp NULL DEFAULT NULL,
  `pdf_token` varchar(255) DEFAULT NULL,
  `approved_by_name` varchar(255) DEFAULT NULL,
  `approval_ip` varchar(255) DEFAULT NULL,
  `approval_method` varchar(255) DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `signature_type` varchar(255) DEFAULT NULL,
  `service_agreement_path` varchar(255) DEFAULT NULL,
  `service_agreement_filename` varchar(255) DEFAULT NULL,
  `service_agreement_generated_at` timestamp NULL DEFAULT NULL,
  `service_agreement_email_status` varchar(40) DEFAULT NULL,
  `service_agreement_emailed_at` timestamp NULL DEFAULT NULL,
  `service_agreement_email_failed_reason` text DEFAULT NULL,
  `service_agreement_email_attempts` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_url` varchar(1000) DEFAULT NULL,
  `image_public_id` varchar(500) DEFAULT NULL,
  `legacy_image_path` varchar(500) DEFAULT NULL,
  `pdf_storage_key` varchar(500) DEFAULT NULL,
  `pdf_storage_file_id` varchar(200) DEFAULT NULL,
  `pdf_storage_url` varchar(1000) DEFAULT NULL,
  `legacy_pdf_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  `quote_pdf_storage_key` varchar(500) DEFAULT NULL,
  `quote_pdf_storage_file_id` varchar(200) DEFAULT NULL,
  `quote_pdf_storage_url` varchar(1000) DEFAULT NULL,
  `service_agreement_storage_file_id` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotations_approval_token_unique` (`approval_token`),
  UNIQUE KEY `quotations_pdf_token_unique` (`pdf_token`),
  KEY `quotations_quote_request_id_index` (`quote_request_id`),
  KEY `quotations_status_index` (`status`),
  CONSTRAINT `quotations_quote_request_id_foreign` FOREIGN KEY (`quote_request_id`) REFERENCES `quote_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotations`
--

LOCK TABLES `quotations` WRITE;
/*!40000 ALTER TABLE `quotations` DISABLE KEYS */;
INSERT INTO `quotations` VALUES (3,2,'KwikShift Movers & Relocators','info@kwikshiftmovers.co.ke','+254 112587581 / +254111330980',NULL,'2026-05-10','2026-05-17',25.00,48,'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.','[{\"name\":\"Transportation & Fuel\",\"description\":\"Moving vehicle rental and fuel\"},{\"name\":\"Labour Charges\",\"description\":\"Professional moving team (2-3 persons)\"},{\"name\":\"Loading & Unloading\",\"description\":\"Professional loading and unloading service\"}]','sample product','50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.','approved','2026-05-11 22:21:12','email',625.00,1,'2026-05-11 22:43:52','MPESA CONFIRMATION','mpesa','https://wa.me/254112587581?text=Hello+roy+okoth%21+%E2%9C%85%0A%0A%2ADeposit+Received%21%2A%0AAmount%3A+KES+625.00%0AReference%3A+MPESA+CONFIRMATION%0A%0A%2AYour+booking+is+now+CONFIRMED%2A+%F0%9F%8E%89%0A%F0%9F%93%85+Move+Date%3A+15+May+2026%0A%F0%9F%93%8D+Pickup%3A+londiani+rd%0A%F0%9F%93%8D+Drop-off%3A+kirinyaga+rd%0A%0ABalance+Due+on+Move+Day%3A+KES+1%2C875.00%0A%0AWe+will+see+you+on+15+May+2026%21+%F0%9F%9A%9B%0A%2AKwikshift+Admin+Panel+Team%2A',NULL,NULL,'londiani rd','kirinyaga rd','2026-05-15',2500.00,'Cyrus Kirop',NULL,'2026-05-12','3874cc69-4472-48f2-b6c1-1a7e8dbc5369','2026-05-18 22:43:29','79e9275f-3509-4779-a30e-7cc9c16ec323','roy okoth','127.0.0.1','Online - email','signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png','image',NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-05-10 14:18:42','2026-05-11 22:43:55',NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,NULL,NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,NULL),(4,1,'KwikShift Movers & Relocators','info@kwikshiftmovers.co.ke','+254 112587581 / +254111330980',NULL,'2026-05-10','2026-05-17',25.00,48,'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.','[{\"name\":\"Transportation & Fuel\",\"description\":\"Moving vehicle rental and fuel\"},{\"name\":\"Labour Charges\",\"description\":\"Professional moving team (2-3 persons)\"},{\"name\":\"Loading & Unloading\",\"description\":\"Professional loading and unloading service\"}]','i need to test this data to see','50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.','draft',NULL,NULL,3750.00,0,NULL,NULL,NULL,NULL,NULL,NULL,'karen','ngong','2026-05-15',15000.00,'Cyrus Kirop',NULL,'2026-05-10','6141ec92-3c14-4bb6-a7b9-c37692152bd9','2026-05-17 14:37:29','13d2602a-ec4a-4181-a46c-c36cd3c84d79',NULL,NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png','image',NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-05-10 14:37:29','2026-05-10 14:37:29',NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,NULL,NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,NULL),(6,4,'KwikShift Movers & Relocators','info@kwikshiftmovers.co.ke','+254 112587581 / +254111330980','','2026-05-13','2026-05-20',50.00,48,'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.','[{\"name\":\"Transportation & Fuel\",\"description\":\"Moving vehicle rental and fuel\"},{\"name\":\"Labour Charges\",\"description\":\"Professional moving team (2-3 persons)\"},{\"name\":\"Loading & Unloading\",\"description\":\"Professional loading and unloading service\"}]',NULL,'50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.','approved','2026-05-12 21:49:16','email',12500.00,0,NULL,NULL,NULL,NULL,NULL,NULL,'Ruiru, Kiambu, Kenya','Landless Road, Kamenu ward, Thika Town, Kiambu, Kenya','2026-05-15',25000.00,'Cyrus Kirop','Sales manager','2026-05-13','196fc523-3b58-408e-930f-28d235a54067','2026-05-19 21:50:06','8586eaae-3d9d-4e27-b343-57b09e430ac8','denica','127.0.0.1','Online - email','signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png','image',NULL,NULL,NULL,NULL,NULL,NULL,0,'2026-05-12 21:49:03','2026-05-12 21:50:06',NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,NULL,NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,NULL),(7,5,'KwikShift Movers & Relocators','info@kwikshiftmovers.co.ke','+254 112587581 / +254111330980','https://kwikshiftmovers.co.ke/','2026-05-13','2026-05-20',10.00,48,'Free cancellation up to 48 hours before the scheduled move date. Cancellations made within 48 hours will incur a cancellation fee.','[{\"name\":\"Transportation & Fuel\",\"description\":\"Moving vehicle rental and fuel\"},{\"name\":\"Labour Charges\",\"description\":\"Professional moving team (2-3 persons)\"},{\"name\":\"Loading & Unloading\",\"description\":\"Professional loading and unloading service\"}]',NULL,'50% deposit required to confirm booking. Remaining balance due on day of move. Accepted payments: M-Pesa, Bank Transfer, Cash.','approved','2026-05-12 22:01:03','email',3200.00,0,NULL,NULL,NULL,NULL,NULL,NULL,'Londiani ward, Kipkelion East, Kericho County, Kenya','Junction Maili Mbili, Hells Gate ward, Naivasha, Nakuru, Kenya','2026-05-15',32000.00,'Cyrus Kirop','Sales manager','2026-05-13','b53bc641-6697-4957-b35c-29ae230461cc','2026-05-19 22:01:45','13cdde6f-b30d-4460-8438-08f2d7cd38b3','denica','127.0.0.1','Online - email','signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png','image','agreements/service_agreement_5_20260513033444_1778632484_nboea2.pdf','service_agreement_5_20260513033444.pdf','2026-05-13 00:34:44','pending',NULL,NULL,0,'2026-05-12 22:00:51','2026-05-13 00:34:50',NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png','agreements/service_agreement_5_20260513033444_1778632484_nboea2.pdf','4_z564b1941714b13ea96e90417_f118a813bc658dddd_d20260513_m003447_c005_v0501042_t0006_u01778632487154','https://f005.backblazeb2.com/file/kwikshift/agreements/service_agreement_5_20260513033444_1778632484_nboea2.pdf?Authorization=3_20260513003448_ba9ad5737b24f6a2a09f700a_d9ad607329f8aaa62af870de753cf747cf90e764_005_20260513013448_0067_dnld','service-agreements/service_agreement_5_20260513010830.pdf','agreements/service_agreement_5_20260513033444_1778632484_nboea2.pdf','https://f005.backblazeb2.com/file/kwikshift/agreements/service_agreement_5_20260513033444_1778632484_nboea2.pdf?Authorization=3_20260513003448_ba9ad5737b24f6a2a09f700a_d9ad607329f8aaa62af870de753cf747cf90e764_005_20260513013448_0067_dnld','signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,NULL,'4_z564b1941714b13ea96e90417_f118a813bc658dddd_d20260513_m003447_c005_v0501042_t0006_u01778632487154');
/*!40000 ALTER TABLE `quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quote_requests`
--

DROP TABLE IF EXISTS `quote_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quote_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `contact_preference` enum('email','whatsapp','both') DEFAULT 'both',
  `whatsapp_url` text DEFAULT NULL,
  `moving_from` varchar(255) DEFAULT NULL,
  `moving_to` varchar(255) DEFAULT NULL,
  `move_date` date DEFAULT NULL,
  `service_type` varchar(255) DEFAULT NULL,
  `move_size` varchar(255) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `source_page` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('new','emailed','email_failed','processing','quoted','created','completed','closed','spam') NOT NULL DEFAULT 'new',
  `approval_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_requests_email_index` (`email`),
  KEY `quote_requests_phone_index` (`phone`),
  KEY `quote_requests_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quote_requests`
--

LOCK TABLES `quote_requests` WRITE;
/*!40000 ALTER TABLE `quote_requests` DISABLE KEYS */;
INSERT INTO `quote_requests` VALUES (1,'cyrus kipruto kirop','kiropcyrus028@gmail.com','0769685995','both',NULL,'karen','ngong','2026-05-15','Residential Relocation','1 Bedroom','i need to test this data to see','/admin/quotes','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko/20100101 Firefox/150.0','created','2026-05-10','2026-05-10 09:00:43'),(2,'roy okoth','hydrasoftke@gmail.com','0112587581','both',NULL,'londiani rd','kirinyaga rd','2026-05-15','Residential Relocation','1 Bedroom','sample product','/admin/quotes','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko/20100101 Firefox/150.0','completed','2026-05-10','2026-05-10 10:48:27'),(4,'denica','fricadenica@gmail.com','0712345678','both',NULL,'Ruiru, Kiambu, Kenya','Landless Road, Kamenu ward, Thika Town, Kiambu, Kenya','2026-05-15','Residential Relocation','Bedsitter',NULL,'/admin/quotes','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko/20100101 Firefox/150.0','created','2026-05-13','2026-05-12 21:48:37'),(5,'denica','fricadenica@gmail.com','0712345678','both',NULL,'Londiani ward, Kipkelion East, Kericho County, Kenya','Junction Maili Mbili, Hells Gate ward, Naivasha, Nakuru, Kenya','2026-05-15','Residential Relocation','1 Bedroom',NULL,'/admin/quotes','127.0.0.1','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:150.0) Gecko/20100101 Firefox/150.0','created','2026-05-13','2026-05-12 22:00:22');
/*!40000 ALTER TABLE `quote_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reviewer_name` varchar(255) NOT NULL,
  `reviewer_role` varchar(255) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `review_message` text NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `moderation_notes` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `source_page` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image_url` varchar(1000) DEFAULT NULL,
  `image_public_id` varchar(500) DEFAULT NULL,
  `legacy_image_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_reviewed_by_foreign` (`reviewed_by`),
  KEY `reviews_status_index` (`status`),
  KEY `reviews_rating_index` (`rating`),
  KEY `reviews_submitted_at_index` (`submitted_at`),
  CONSTRAINT `reviews_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
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
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `todo_tasks`
--

DROP TABLE IF EXISTS `todo_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `todo_tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'assigned',
  `priority` varchar(40) NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `todo_tasks_user_id_status_due_date_index` (`user_id`,`status`,`due_date`),
  KEY `todo_tasks_user_id_priority_index` (`user_id`,`priority`),
  CONSTRAINT `todo_tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `todo_tasks`
--

LOCK TABLES `todo_tasks` WRITE;
/*!40000 ALTER TABLE `todo_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `todo_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `otp_code` varchar(512) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `otp_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `image_url` varchar(1000) DEFAULT NULL,
  `image_public_id` varchar(500) DEFAULT NULL,
  `legacy_image_path` varchar(500) DEFAULT NULL,
  `storage_key` varchar(500) DEFAULT NULL,
  `storage_url` varchar(1000) DEFAULT NULL,
  `legacy_file_path` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Cyrus Kirop','hydrasoftke@gmail.com','2026-05-09 15:46:19',NULL,'Sales manager',NULL,NULL,NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png','$2y$12$59IU9LapK1CDDQBBiyiTUufBS3dnnhL3G/HiBqltJf4PUTcV8tHoO','scz4KeNAwelDVgGHpW5iGEO8U5e5Zo6HVNKu50NHCgHUiOlZTovVvG07Gq9r',0,NULL,NULL,0,'2026-05-12 21:45:47','127.0.0.1','2026-05-09 15:46:19','2026-05-12 21:46:11','signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png',NULL,NULL,'signatures/signature-1-854ed1e2-05e8-429d-8552-c94952c45547.png');
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

-- Dump completed on 2026-05-13  3:37:09
