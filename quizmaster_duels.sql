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
-- Table structure for table `duels`
--

DROP TABLE IF EXISTS `duels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `duels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `challenger_id` int NOT NULL,
  `opponent_id` int NOT NULL,
  `categorie_id` int DEFAULT NULL,
  `difficulte_id` int DEFAULT NULL,
  `type` enum('timed','accuracy','mixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_limit` int DEFAULT '0',
  `question_count` int DEFAULT '10',
  `status` enum('pending','active','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `winner_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `challenger_id` (`challenger_id`),
  KEY `opponent_id` (`opponent_id`),
  KEY `categorie_id` (`categorie_id`),
  KEY `difficulte_id` (`difficulte_id`),
  KEY `winner_id` (`winner_id`),
  CONSTRAINT `duels_ibfk_1` FOREIGN KEY (`challenger_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `duels_ibfk_2` FOREIGN KEY (`opponent_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `duels_ibfk_3` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `duels_ibfk_4` FOREIGN KEY (`difficulte_id`) REFERENCES `difficultes` (`id`),
  CONSTRAINT `duels_ibfk_5` FOREIGN KEY (`winner_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `duels`
--

LOCK TABLES `duels` WRITE;
/*!40000 ALTER TABLE `duels` DISABLE KEYS */;
INSERT INTO `duels` VALUES (1,21,1,NULL,NULL,'timed',NULL,10,'pending',NULL,NULL,NULL),(2,1,21,NULL,NULL,'timed',0,10,'cancelled',NULL,NULL,NULL),(3,21,1,NULL,NULL,'timed',0,10,'cancelled',NULL,NULL,NULL),(7,21,1,NULL,NULL,'accuracy',0,10,'active','2025-04-21 14:16:49',NULL,NULL),(8,21,1,NULL,NULL,'accuracy',0,10,'active','2025-04-21 15:07:55',NULL,NULL),(9,1,21,NULL,NULL,'timed',0,10,'active','2025-04-21 15:47:44',NULL,NULL),(12,21,1,NULL,NULL,'timed',0,10,'completed','2025-04-21 17:52:13','2025-04-21 17:53:39',21),(13,21,1,NULL,NULL,'accuracy',0,10,'completed','2025-04-21 17:55:23','2025-04-21 19:19:31',NULL),(14,21,1,NULL,NULL,'mixed',0,10,'completed','2025-04-21 18:02:14','2025-04-21 18:02:39',NULL),(15,21,1,NULL,NULL,'timed',0,10,'completed','2025-04-21 18:05:07','2025-04-21 19:08:11',1),(17,21,1,NULL,NULL,'timed',0,10,'completed','2025-04-21 19:23:41','2025-04-21 19:26:04',21),(19,21,1,NULL,NULL,'timed',0,10,'completed','2025-04-21 20:26:30','2025-04-21 20:28:33',21),(20,21,1,NULL,NULL,'timed',0,10,'completed','2025-04-21 21:44:49','2025-04-21 21:47:53',1),(21,1,21,NULL,NULL,'timed',0,10,'completed','2025-04-21 22:29:12','2025-04-21 22:31:02',1),(24,1,21,NULL,NULL,'timed',0,10,'completed','2025-04-22 12:38:45','2025-04-22 13:04:37',21),(25,21,1,NULL,NULL,'timed',0,10,'completed','2025-04-22 12:39:34','2025-04-22 12:50:25',1),(26,1,21,NULL,NULL,'timed',0,10,'completed','2025-04-22 21:14:55','2025-04-22 21:16:21',1),(27,1,21,NULL,NULL,'timed',0,10,'completed','2025-04-23 13:25:14','2025-04-23 13:25:42',1),(28,1,21,NULL,NULL,'timed',0,10,'completed','2025-04-24 10:50:56','2025-04-24 10:53:29',1),(29,1,21,NULL,NULL,'timed',0,10,'active','2025-04-24 13:53:47',NULL,NULL);
/*!40000 ALTER TABLE `duels` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-24 14:17:17
