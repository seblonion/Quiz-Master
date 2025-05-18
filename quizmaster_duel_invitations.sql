-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: quizmaster
-- ------------------------------------------------------
-- Server version	8.0.39

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
-- Table structure for table `duel_invitations`
--

DROP TABLE IF EXISTS `duel_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `duel_invitations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `duel_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `recipient_id` int NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','accepted','declined') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `duel_id` (`duel_id`),
  KEY `sender_id` (`sender_id`),
  KEY `recipient_id` (`recipient_id`),
  CONSTRAINT `duel_invitations_ibfk_1` FOREIGN KEY (`duel_id`) REFERENCES `duels` (`id`),
  CONSTRAINT `duel_invitations_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `duel_invitations_ibfk_3` FOREIGN KEY (`recipient_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `duel_invitations`
--

LOCK TABLES `duel_invitations` WRITE;
/*!40000 ALTER TABLE `duel_invitations` DISABLE KEYS */;
INSERT INTO `duel_invitations` VALUES (1,1,21,1,'ok','declined','2025-04-20 14:25:03','2025-04-21 16:25:03'),(2,2,1,21,'','declined','2025-04-20 14:28:48','2025-04-21 16:28:48'),(3,3,21,1,'','declined','2025-04-20 14:36:51','2025-04-21 16:36:51'),(7,7,21,1,'','accepted','2025-04-21 12:16:34','2025-04-22 14:16:34'),(8,8,21,1,'','accepted','2025-04-21 13:07:44','2025-04-22 15:07:44'),(9,9,1,21,'','accepted','2025-04-21 13:47:18','2025-04-22 15:47:18'),(12,12,21,1,'','accepted','2025-04-21 15:51:45','2025-04-22 17:51:45'),(13,13,21,1,'','accepted','2025-04-21 15:55:11','2025-04-22 17:55:11'),(14,14,21,1,'','accepted','2025-04-21 16:02:05','2025-04-22 18:02:05'),(15,15,21,1,'','accepted','2025-04-21 16:04:50','2025-04-22 18:04:50'),(17,17,21,1,'','accepted','2025-04-21 17:23:19','2025-04-22 19:23:19'),(19,19,21,1,'','accepted','2025-04-21 18:26:18','2025-04-22 20:26:18'),(20,20,21,1,'','accepted','2025-04-21 19:44:38','2025-04-22 21:44:38'),(21,21,1,21,'','accepted','2025-04-21 20:28:39','2025-04-22 22:28:39'),(24,24,1,21,'','accepted','2025-04-22 10:38:34','2025-04-23 12:38:34'),(25,25,21,1,'','accepted','2025-04-22 10:39:23','2025-04-23 12:39:23'),(26,26,1,21,'','accepted','2025-04-22 19:14:49','2025-04-23 21:14:49'),(27,27,1,21,'','accepted','2025-04-23 11:24:58','2025-04-24 13:24:58'),(28,28,1,21,'','accepted','2025-04-24 08:50:42','2025-04-25 10:50:42'),(29,29,1,21,'','accepted','2025-04-24 11:50:19','2025-04-25 13:50:19');
/*!40000 ALTER TABLE `duel_invitations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-24 14:17:16
