-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: online_voting_system
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_profiles`
--

DROP TABLE IF EXISTS `admin_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `age` int(10) unsigned NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_profiles_user_id_foreign` (`user_id`),
  CONSTRAINT `admin_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_profiles`
--

LOCK TABLES `admin_profiles` WRITE;
/*!40000 ALTER TABLE `admin_profiles` DISABLE KEYS */;
INSERT INTO `admin_profiles` VALUES (1,1,35,'9800000000','2026-07-16 18:38:13','2026-07-16 18:38:13'),(2,6,22,'9810000000','2026-08-27 06:31:08','2026-08-27 06:31:08');
/*!40000 ALTER TABLE `admin_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `election_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `logged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_logged_at_index` (`logged_at`),
  KEY `audit_logs_election_id_foreign` (`election_id`),
  CONSTRAINT `audit_logs_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,16,'report_exported','Election Results Report exported in PDF format.','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','{\"report_type\":\"election-results\",\"format\":\"pdf\",\"election\":\"Abcd\"}','2026-08-27 05:51:01','2026-08-27 05:51:01','2026-08-27 05:51:01'),(2,1,16,'report_exported','Voter List Report exported in EXCEL format.','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','{\"report_type\":\"voter-list\",\"format\":\"excel\",\"election\":\"Abcd\"}','2026-08-27 05:51:20','2026-08-27 05:51:20','2026-08-27 05:51:20');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `party` varchar(255) DEFAULT NULL,
  `age` int(10) unsigned NOT NULL,
  `position` varchar(255) NOT NULL DEFAULT 'Election Representative',
  `image_path` varchar(255) DEFAULT NULL,
  `vision_path` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `candidates_email_unique` (`email`),
  KEY `candidates_election_id_foreign` (`election_id`),
  CONSTRAINT `candidates_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidates`
--

LOCK TABLES `candidates` WRITE;
/*!40000 ALTER TABLE `candidates` DISABLE KEYS */;
INSERT INTO `candidates` VALUES (1,1,'Emmitt Makwanpur','Independent',61,'President',NULL,NULL,'candidate10@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(2,1,'Jermaine Makwanpur','Forward Nepal',50,'President',NULL,NULL,'candidate20@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(3,1,'Trever Makwanpur','Independent',68,'Vice President',NULL,NULL,'candidate30@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(4,1,'Ellen Makwanpur','Independent',41,'Vice President',NULL,NULL,'candidate40@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(5,2,'Kolby Kathmandu','Unity Party',35,'President',NULL,NULL,'candidate11@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(6,2,'Hillard Kathmandu','Forward Nepal',44,'President',NULL,NULL,'candidate21@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(7,2,'Bette Kathmandu','Unity Party',37,'Vice President',NULL,NULL,'candidate31@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(8,2,'Kathryne Kathmandu','Forward Nepal',57,'Vice President',NULL,NULL,'candidate41@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(9,3,'Bennie Lalitpur','Independent',50,'President',NULL,NULL,'candidate12@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(10,3,'Isai Lalitpur','Unity Party',56,'President',NULL,NULL,'candidate22@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(11,3,'Aracely Lalitpur','Independent',49,'Vice President',NULL,NULL,'candidate32@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(12,3,'Janae Lalitpur','Citizen Forum',43,'Vice President',NULL,NULL,'candidate42@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(13,4,'Albin Bhaktapur','Citizen Forum',35,'President',NULL,NULL,'candidate13@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(14,4,'Clifton Bhaktapur','Independent',67,'President',NULL,NULL,'candidate23@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(15,4,'Brennon Bhaktapur','Independent',45,'Vice President',NULL,NULL,'candidate33@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(16,4,'Toy Bhaktapur','Forward Nepal',41,'Vice President',NULL,NULL,'candidate43@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(17,5,'Johathan Chitwan','Independent',57,'President',NULL,NULL,'candidate14@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(18,5,'Bernadine Chitwan','Forward Nepal',37,'President',NULL,NULL,'candidate24@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(19,5,'Ralph Chitwan','Independent',60,'Vice President',NULL,NULL,'candidate34@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(20,5,'Carli Chitwan','Independent',59,'Vice President',NULL,NULL,'candidate44@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(21,6,'Keeley Pokhara','Independent',43,'President',NULL,NULL,'candidate15@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(22,6,'Agnes Pokhara','Forward Nepal',35,'President',NULL,NULL,'candidate25@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(23,6,'Lenna Pokhara','Citizen Forum',62,'Vice President',NULL,NULL,'candidate35@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(24,6,'Saige Pokhara','Citizen Forum',66,'Vice President',NULL,NULL,'candidate45@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(25,7,'Shany Biratnagar','Unity Party',61,'President',NULL,NULL,'candidate16@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(26,7,'Twila Biratnagar','Unity Party',47,'President',NULL,NULL,'candidate26@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(27,7,'Kyra Biratnagar','Citizen Forum',44,'Vice President',NULL,NULL,'candidate36@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(28,7,'Thaddeus Biratnagar','Unity Party',47,'Vice President',NULL,NULL,'candidate46@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(29,8,'Daron Dharan','Unity Party',55,'President',NULL,NULL,'candidate17@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(30,8,'Beatrice Dharan','Forward Nepal',46,'President',NULL,NULL,'candidate27@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(31,8,'Gideon Dharan','Forward Nepal',45,'Vice President',NULL,NULL,'candidate37@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(32,8,'Gregorio Dharan','Independent',67,'Vice President',NULL,NULL,'candidate47@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(37,10,'Asha Butwal','Forward Nepal',43,'President',NULL,NULL,'candidate19@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(38,10,'Pansy Butwal','Independent',51,'President',NULL,NULL,'candidate29@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(39,10,'Kristina Butwal','Unity Party',68,'Vice President',NULL,NULL,'candidate39@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(40,10,'Vilma Butwal','Citizen Forum',59,'Vice President',NULL,NULL,'candidate49@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(41,11,'Audie Nepalgunj','Forward Nepal',60,'President',NULL,NULL,'candidate110@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(42,11,'Rosalinda Nepalgunj','Forward Nepal',38,'President',NULL,NULL,'candidate210@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(43,11,'Robin Nepalgunj','Unity Party',62,'Vice President',NULL,NULL,'candidate310@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(44,11,'Marjorie Nepalgunj','Unity Party',43,'Vice President',NULL,NULL,'candidate410@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(45,12,'Diego Dhangadhi','Citizen Forum',62,'President',NULL,NULL,'candidate111@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(46,12,'Nigel Dhangadhi','Unity Party',50,'President',NULL,NULL,'candidate211@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(47,12,'Pink Dhangadhi','Forward Nepal',43,'Vice President',NULL,NULL,'candidate311@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(48,12,'Mose Dhangadhi','Citizen Forum',46,'Vice President',NULL,NULL,'candidate411@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(49,13,'Mable Janakpur','Independent',50,'President',NULL,NULL,'candidate112@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(50,13,'Brendon Janakpur','Forward Nepal',61,'President',NULL,NULL,'candidate212@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(51,13,'Elbert Janakpur','Unity Party',52,'Vice President',NULL,NULL,'candidate312@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(52,13,'Felton Janakpur','Citizen Forum',66,'Vice President',NULL,NULL,'candidate412@example.com',1,'2026-07-16 18:38:13','2026-07-16 18:38:13');
/*!40000 ALTER TABLE `candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deleted_candidates`
--

DROP TABLE IF EXISTS `deleted_candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deleted_candidates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_archive_id` bigint(20) unsigned DEFAULT NULL,
  `original_candidate_id` bigint(20) unsigned DEFAULT NULL,
  `election_name` varchar(255) NOT NULL,
  `candidate_name` varchar(255) NOT NULL,
  `party` varchar(255) DEFAULT NULL,
  `age` int(10) unsigned DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `vision_path` varchar(255) DEFAULT NULL,
  `vote_count` int(10) unsigned NOT NULL DEFAULT 0,
  `deleted_reason` varchar(255) NOT NULL DEFAULT 'candidate_deleted',
  `election_started_at` timestamp NULL DEFAULT NULL,
  `election_ended_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `restored_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deleted_candidates_election_archive_id_foreign` (`election_archive_id`),
  CONSTRAINT `deleted_candidates_election_archive_id_foreign` FOREIGN KEY (`election_archive_id`) REFERENCES `election_archives` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deleted_candidates`
--

LOCK TABLES `deleted_candidates` WRITE;
/*!40000 ALTER TABLE `deleted_candidates` DISABLE KEYS */;
/*!40000 ALTER TABLE `deleted_candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_archives`
--

DROP TABLE IF EXISTS `election_archives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election_archives` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` bigint(20) unsigned DEFAULT NULL,
  `election_name` varchar(255) NOT NULL,
  `election_title` varchar(255) DEFAULT NULL,
  `archive_reason` varchar(255) NOT NULL DEFAULT 'deleted',
  `candidate_count` int(10) unsigned NOT NULL DEFAULT 0,
  `total_votes` int(10) unsigned NOT NULL DEFAULT 0,
  `election_started_at` timestamp NULL DEFAULT NULL,
  `election_ended_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `restored_at` timestamp NULL DEFAULT NULL,
  `winners` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`winners`)),
  `position_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`position_results`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `election_archives_host_id_foreign` (`host_id`),
  CONSTRAINT `election_archives_host_id_foreign` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_archives`
--

LOCK TABLES `election_archives` WRITE;
/*!40000 ALTER TABLE `election_archives` DISABLE KEYS */;
/*!40000 ALTER TABLE `election_archives` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_places`
--

DROP TABLE IF EXISTS `election_places`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election_places` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `election_places_election_id_unique` (`election_id`),
  CONSTRAINT `election_places_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_places`
--

LOCK TABLES `election_places` WRITE;
/*!40000 ALTER TABLE `election_places` DISABLE KEYS */;
INSERT INTO `election_places` VALUES (1,14,'Hetauda chetra no 2','2026-07-16 18:44:24','2026-07-16 18:44:24'),(3,16,'Abcd','2026-08-27 05:48:41','2026-08-27 05:48:41'),(4,17,'Bcd','2026-08-27 06:29:39','2026-08-27 06:29:39'),(5,18,'bbs','2026-08-27 14:14:02','2026-08-27 14:14:02'),(6,19,'bca','2026-08-27 14:14:39','2026-08-27 14:14:39');
/*!40000 ALTER TABLE `election_places` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `election_settings`
--

DROP TABLE IF EXISTS `election_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `election_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `election_id` bigint(20) unsigned DEFAULT NULL,
  `election_title` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `started_at` timestamp NULL DEFAULT NULL,
  `paused_at` timestamp NULL DEFAULT NULL,
  `remaining_seconds` int(10) unsigned DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `election_settings_election_id_foreign` (`election_id`),
  CONSTRAINT `election_settings_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `election_settings`
--

LOCK TABLES `election_settings` WRITE;
/*!40000 ALTER TABLE `election_settings` DISABLE KEYS */;
INSERT INTO `election_settings` VALUES (1,1,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(2,2,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(3,3,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(4,4,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(5,5,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(6,6,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(7,7,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(8,8,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(10,10,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(11,11,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(12,12,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(13,13,NULL,0,NULL,NULL,NULL,'2026-07-18 18:38:14',NULL,'2026-07-16 18:38:14','2026-07-16 18:39:12'),(14,14,'Hetauda Election Student',0,NULL,NULL,NULL,NULL,NULL,'2026-07-16 18:44:24','2026-07-16 18:44:24'),(16,16,'Abcd',0,NULL,NULL,NULL,NULL,NULL,'2026-08-27 05:48:41','2026-08-27 05:48:41'),(17,17,'Abcd',0,NULL,NULL,NULL,NULL,NULL,'2026-08-27 06:29:39','2026-08-27 06:29:39'),(18,18,'clz',0,NULL,NULL,NULL,NULL,NULL,'2026-08-27 14:14:02','2026-08-27 14:14:02'),(19,19,'Abcd',0,NULL,NULL,NULL,NULL,NULL,'2026-08-27 14:14:39','2026-08-27 14:14:39');
/*!40000 ALTER TABLE `election_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `elections`
--

DROP TABLE IF EXISTS `elections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `elections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `host_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `elections_host_id_foreign` (`host_id`),
  CONSTRAINT `elections_host_id_foreign` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `elections`
--

LOCK TABLES `elections` WRITE;
/*!40000 ALTER TABLE `elections` DISABLE KEYS */;
INSERT INTO `elections` VALUES (1,NULL,'Makwanpur',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(2,NULL,'Kathmandu',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(3,NULL,'Lalitpur',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(4,NULL,'Bhaktapur',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(5,NULL,'Chitwan',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(6,NULL,'Pokhara',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(7,NULL,'Biratnagar',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(8,NULL,'Dharan',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(10,NULL,'Butwal',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(11,NULL,'Nepalgunj',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(12,NULL,'Dhangadhi',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(13,NULL,'Janakpur',1,'2026-07-16 18:38:12','2026-07-16 18:38:12'),(14,NULL,'Makwanpur',1,'2026-07-16 18:44:24','2026-07-16 18:44:24'),(16,NULL,'Abcd',1,'2026-08-27 05:48:41','2026-08-27 05:48:41'),(17,NULL,'Abcd',1,'2026-08-27 06:29:39','2026-08-27 06:29:39'),(18,7,'Makwanpur',1,'2026-08-27 14:14:02','2026-08-27 14:14:02'),(19,7,'Makwanpur',1,'2026-08-27 14:14:39','2026-08-27 14:14:39');
/*!40000 ALTER TABLE `elections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `host_profiles`
--

DROP TABLE IF EXISTS `host_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `reason_type` varchar(255) NOT NULL,
  `reason_message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `host_profiles_user_id_unique` (`user_id`),
  CONSTRAINT `host_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host_profiles`
--

LOCK TABLES `host_profiles` WRITE;
/*!40000 ALTER TABLE `host_profiles` DISABLE KEYS */;
INSERT INTO `host_profiles` VALUES (1,7,'Random','hsdhsbfhdsbf asnjnd xnasjnjsa jnasjnd jxnasjd jasjdnjn ajbjasd nsajdndsaj jnsandwan andjsand','2026-08-27 14:12:10','2026-08-27 14:12:10');
/*!40000 ALTER TABLE `host_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(5,'2026_05_11_115752_create_candidates_table',1),(6,'2026_05_11_115753_create_admin_profiles_table',1),(7,'2026_05_11_115754_create_election_settings_table',1),(8,'2026_05_11_115754_create_votes_table',1),(10,'2026_05_12_090000_update_votes_for_multi_position_ballot',1),(12,'2026_05_15_000000_upgrade_election_lifecycle_and_archives',1),(13,'2026_05_15_010000_add_pause_fields_to_election_settings',1),(14,'2026_05_15_020000_add_restored_at_to_archives',1),(16,'2026_05_21_000001_create_audit_logs_table',1),(17,'2026_05_21_000002_add_party_to_candidates_and_deleted_candidates',1),(18,'2026_05_22_000001_add_unique_index_to_users_citizenship_number',1),(19,'2026_05_23_000000_add_election_title_to_election_settings_and_archives',1),(21,'2026_08_27_010000_remove_identity_fields_from_users_table',2),(23,'2026_05_11_115751_create_elections_table',3),(24,'2026_05_14_090000_add_election_id_to_election_settings_table',3),(25,'2026_05_15_030000_add_last_known_election_to_users',3),(26,'2026_07_16_000000_create_election_places_table',3),(27,'2026_08_27_020000_rename_legacy_schema_to_election_schema',3),(28,'2026_08_27_030000_add_host_account_support',4),(29,'2026_08_27_040000_rename_legacy_database_keys_to_election_names',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
INSERT INTO `sessions` VALUES ('0YUj7hhJGUeU4vdauOyd0i3BzTB28yqH9nBEtLnU',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26100; en-US) PowerShell/7.6.4','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaHhHMTJyVERVdG9MQzBjNlBhbGIzaWxSUDdVeE5tMFNHdjZ0VjJqQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWdpc3RlciI7czo1OiJyb3V0ZSI7czo4OiJyZWdpc3RlciI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787835806),('3tE2tRibQGUlgppz1XRqM9rIcenuz2e35AyQYtYp',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26100; en-US) PowerShell/7.6.4','YTozOntzOjY6Il90b2tlbiI7czo0MDoid2Y2ZWp4YW1Zbk9DVm80dEJyeVNVTmM4UlNKS28zOU9tVkg3OGRDZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787839591),('HwZ76r59ozxoyMD7Y4aWqkKVhnMRaRVFWYfJDNwY',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0ZDM3I3a2hoTEJpS044RUswTjd3bXpuU1ZHd2hSb2NveWMxbXljSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fX0=',1787840242),('MF7InowTBDS5VmHAMzbgtiFmckWRNvGA2xpMMFjl',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26100; en-US) PowerShell/7.6.4','YTozOntzOjY6Il90b2tlbiI7czo0MDoiaTZ3dXRqVWpocGt0TEM1QTA1dDhUVGFFM0huenhrN2o2QTc0WGd1QSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9ob3N0cy9jcmVhdGUiO3M6NToicm91dGUiO3M6MTI6Imhvc3RzLmNyZWF0ZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1787839592);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user','host') NOT NULL DEFAULT 'user',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `date_of_birth` date DEFAULT NULL,
  `election_id` bigint(20) unsigned DEFAULT NULL,
  `last_known_election_name` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `rejection_message` text DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `has_voted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_contact_number_unique` (`contact_number`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Admin','9800000000',NULL,'$2y$12$aTOe9wOnUV8awvaUoYJ8ZuOCOxzVUHO4NNoSmt0WwedFwUINvWaq6','admin','approved',NULL,NULL,NULL,NULL,NULL,'2026-07-16 18:38:12',NULL,NULL,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(2,'Approved User','9811111111',NULL,'$2y$12$gD3YRs5tvN0MRbbh77dpKusI0A/dUb6tw1qnCmg8a8dTyHiIOVPxC','user','approved','1998-04-17',1,NULL,NULL,NULL,'2026-07-16 18:38:13',NULL,NULL,'2026-07-16 18:38:13','2026-07-16 18:38:13'),(3,'Pending User','9822222222',NULL,'$2y$12$f.FQWrlgk7lyj6Gq00YqQ.FLmSQl50XvwydFJjqIKyX3EHfbMAqIC','user','pending','2000-08-10',2,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-16 18:38:14','2026-07-16 18:38:14'),(4,'Rejected User','9833333333',NULL,'$2y$12$76aQ7NGtO.8s1X7Y7Eb1hu4oPp2bv8WBd8/URFDCeyqK1rnjIhRFm','user','rejected','2001-01-19',3,NULL,NULL,'You can try once again',NULL,NULL,NULL,'2026-07-16 18:38:14','2026-07-16 18:38:14'),(5,'fsdfjsdfj','9800000001',NULL,'$2y$12$3M3lZe.JIHWJFPr8jeJKD.LY.L8gF.SgUSGvy7O2fP5kkejSoZvGu','user','pending','2008-08-27',14,'Makwanpur','users/bkbPek3FAlhPsaVFi6IMJF9VARTkTconrxFp7sq3.jpg',NULL,NULL,NULL,NULL,'2026-08-26 19:11:19','2026-08-26 19:11:19'),(6,'ShahAlam','9810000000',NULL,'$2y$12$.MLI9Tlgiq7ceoRZgS.kwu9wmKsEMgdUgn5WBkqDFSOQgEaDoUG/K','admin','approved',NULL,NULL,NULL,NULL,NULL,'2026-08-27 06:31:08',NULL,NULL,'2026-08-27 06:31:08','2026-08-27 06:31:34'),(7,'Sheikh Sahalam','9814276478','dipeshpokhrel91@gmail.com','$2y$12$.21ADOFmdmkyfEm4vsVq2O.DxVnLlshoWOhZ9iI.qbYC84aDKKpJa','host','approved',NULL,NULL,NULL,'hosts/kLmZXPNKQWBtV9pUnqS1J19aORGeOxO24rmjYH3i.jpg',NULL,'2026-08-27 14:12:55',NULL,NULL,'2026-08-27 14:12:10','2026-08-27 14:12:55');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `votes`
--

DROP TABLE IF EXISTS `votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `votes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `election_id` bigint(20) unsigned NOT NULL,
  `candidate_id` bigint(20) unsigned NOT NULL,
  `position` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `votes_user_id_position_unique` (`user_id`,`position`),
  KEY `votes_candidate_id_foreign` (`candidate_id`),
  KEY `votes_user_id_index` (`user_id`),
  KEY `votes_election_id_foreign` (`election_id`),
  CONSTRAINT `votes_candidate_id_foreign` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_election_id_foreign` FOREIGN KEY (`election_id`) REFERENCES `elections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `votes`
--

LOCK TABLES `votes` WRITE;
/*!40000 ALTER TABLE `votes` DISABLE KEYS */;
/*!40000 ALTER TABLE `votes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-27 20:34:57
