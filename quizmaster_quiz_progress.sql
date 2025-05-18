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
-- Table structure for table `quiz_progress`
--

DROP TABLE IF EXISTS `quiz_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `categorie_id` int NOT NULL,
  `difficulte_id` int NOT NULL,
  `type` varchar(20) DEFAULT 'standard',
  `quiz_id` int DEFAULT NULL,
  `progress_data` text,
  `current_question_index` int NOT NULL DEFAULT '0',
  `answers` json NOT NULL,
  `time_elapsed` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_quiz_unique` (`utilisateur_id`,`categorie_id`,`difficulte_id`,`type`,`quiz_id`),
  KEY `categorie_id` (`categorie_id`),
  KEY `difficulte_id` (`difficulte_id`),
  CONSTRAINT `quiz_progress_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_progress_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_progress_ibfk_3` FOREIGN KEY (`difficulte_id`) REFERENCES `difficultes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1270 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz_progress`
--

LOCK TABLES `quiz_progress` WRITE;
/*!40000 ALTER TABLE `quiz_progress` DISABLE KEYS */;
INSERT INTO `quiz_progress` VALUES (965,1,3,2,'standard',NULL,NULL,9,'{\"258\": \"1031\", \"259\": \"1034\", \"263\": \"1049\"}',9,'2025-04-17 21:25:46','2025-04-18 21:28:02'),(1225,1,2,2,'standard',NULL,NULL,0,'{\"292\": \"1167\", \"398\": \"1589\"}',11,'2025-04-18 13:19:11','2025-04-18 13:24:34'),(1229,1,3,1,'standard',NULL,NULL,9,'{\"12\": \"47\", \"40\": \"158\", \"83\": \"329\", \"86\": \"342\", \"245\": \"979\", \"248\": null, \"249\": \"993\", \"250\": \"998\", \"251\": \"1001\", \"252\": \"1005\"}',8,'2025-04-18 21:26:02','2025-04-18 21:27:20'),(1250,21,4,1,'standard',NULL,NULL,0,'{\"95\": null}',7,'2025-04-21 16:04:44','2025-04-21 16:04:44'),(1251,21,4,1,'standard',NULL,NULL,1,'{\"93\": \"369\", \"95\": null}',1,'2025-04-21 16:04:46','2025-04-21 16:04:46'),(1252,21,4,1,'standard',NULL,NULL,2,'{\"93\": \"369\", \"95\": null, \"302\": null}',1,'2025-04-21 16:04:48','2025-04-21 16:04:48'),(1253,21,4,1,'standard',NULL,NULL,3,'{\"93\": \"369\", \"95\": null, \"302\": null, \"304\": null}',0,'2025-04-21 16:04:49','2025-04-21 16:04:49'),(1254,21,4,1,'standard',NULL,NULL,4,'{\"46\": \"181\", \"93\": \"369\", \"95\": null, \"302\": null, \"304\": null}',3,'2025-04-21 16:04:57','2025-04-21 16:04:57'),(1255,21,4,1,'standard',NULL,NULL,5,'{\"46\": \"181\", \"93\": \"369\", \"95\": null, \"301\": \"1201\", \"302\": null, \"304\": null}',4,'2025-04-21 16:05:02','2025-04-21 16:05:02'),(1256,21,4,1,'standard',NULL,NULL,6,'{\"15\": \"59\", \"46\": \"181\", \"93\": \"369\", \"95\": null, \"301\": \"1201\", \"302\": null, \"304\": null}',5,'2025-04-21 16:05:09','2025-04-21 16:05:09'),(1257,21,4,1,'standard',NULL,NULL,7,'{\"15\": \"59\", \"46\": \"181\", \"93\": \"369\", \"95\": null, \"297\": \"1186\", \"301\": \"1201\", \"302\": null, \"304\": null}',4,'2025-04-21 16:05:14','2025-04-21 16:05:14'),(1258,21,4,1,'standard',NULL,NULL,8,'{\"15\": \"59\", \"46\": \"181\", \"93\": \"369\", \"94\": \"373\", \"95\": null, \"297\": \"1186\", \"301\": \"1201\", \"302\": null, \"304\": null}',5,'2025-04-21 16:05:21','2025-04-21 16:05:21'),(1259,21,4,1,'standard',NULL,NULL,9,'{\"15\": \"59\", \"45\": \"177\", \"46\": \"181\", \"93\": \"369\", \"94\": \"373\", \"95\": null, \"297\": \"1186\", \"301\": \"1201\", \"302\": null, \"304\": null}',7,'2025-04-21 16:05:29','2025-04-21 16:05:29'),(1260,1,4,1,'standard',NULL,NULL,0,'{\"298\": \"1190\"}',7,'2025-04-23 13:23:04','2025-04-23 13:23:04'),(1261,1,4,1,'standard',NULL,NULL,1,'{\"132\": null, \"298\": \"1190\"}',1,'2025-04-23 13:23:08','2025-04-23 13:23:08'),(1262,1,4,1,'standard',NULL,NULL,2,'{\"132\": null, \"298\": \"1190\", \"306\": null}',0,'2025-04-23 13:23:10','2025-04-23 13:23:10'),(1263,1,4,1,'standard',NULL,NULL,3,'{\"132\": null, \"298\": \"1190\", \"300\": \"1198\", \"306\": null}',4,'2025-04-23 13:23:16','2025-04-23 13:23:16'),(1264,1,4,1,'standard',NULL,NULL,4,'{\"132\": null, \"298\": \"1190\", \"299\": \"1194\", \"300\": \"1198\", \"306\": null}',5,'2025-04-23 13:23:22','2025-04-23 13:23:22'),(1265,1,4,1,'standard',NULL,NULL,5,'{\"132\": null, \"298\": \"1190\", \"299\": \"1194\", \"300\": \"1198\", \"305\": \"1219\", \"306\": null}',2,'2025-04-23 13:23:25','2025-04-23 13:23:25'),(1266,1,4,1,'standard',NULL,NULL,6,'{\"96\": \"381\", \"132\": null, \"298\": \"1190\", \"299\": \"1194\", \"300\": \"1198\", \"305\": \"1219\", \"306\": null}',8,'2025-04-23 13:23:35','2025-04-23 13:23:35'),(1267,1,4,1,'standard',NULL,NULL,7,'{\"96\": \"381\", \"132\": null, \"298\": \"1190\", \"299\": \"1194\", \"300\": \"1198\", \"303\": \"1210\", \"305\": \"1219\", \"306\": null}',4,'2025-04-23 13:23:40','2025-04-23 13:23:40'),(1268,1,4,2,'standard',NULL,NULL,0,'{\"376\": \"1504\"}',6,'2025-04-24 13:45:37','2025-04-24 13:45:37'),(1269,1,4,2,'standard',NULL,NULL,1,'{\"98\": \"390\", \"376\": \"1504\"}',3,'2025-04-24 13:45:43','2025-04-24 13:45:43');
/*!40000 ALTER TABLE `quiz_progress` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-24 14:17:15
