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
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `est_admin` tinyint(1) NOT NULL DEFAULT '0',
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `est_contributeur` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,1,'Admin','admin@quizmaster.fr','$2y$10$ie2U.UN0zbu7TsypwWTDbutyU6C97EoYVeUDk6xqArXD9G2A5PrRW','2025-04-17 17:38:54',1,1),(2,0,'Jean Dupont','jean.dupont@gmail.com','password123_hashed','2024-11-15 14:30:00',0,1),(3,0,'Marie Martin','marie.martin@yahoo.fr','pass456_hashed','2024-12-01 09:15:00',0,1),(4,0,'Luc Durand','luc.durand@outlook.com','luc789_hashed','2024-10-20 16:45:00',0,1),(5,0,'Sophie Bernard','sophie.bernard@gmail.com','sophie101_hashed','2024-11-10 12:00:00',0,1),(6,0,'Thomas Lefevre','thomas.lefevre@free.fr','thomas202_hashed','2024-12-05 08:30:00',0,1),(7,0,'Emma Petit','emma.petit@hotmail.com','emma303_hashed','2024-10-25 17:20:00',0,1),(8,0,'Paul Robert','paul.robert@gmail.com','paul404_hashed','2024-11-30 11:10:00',0,1),(9,0,'Clara Simon','clara.simon@orange.fr','clara505_hashed','2024-12-10 13:40:00',0,1),(10,0,'Louis Dubois','louis.dubois@yahoo.com','louis606_hashed','2024-10-15 15:50:00',0,1),(11,0,'Anna Richard','anna.richard@gmail.com','anna707_hashed','2024-11-20 10:25:00',0,1),(12,0,'Hugo Garcia','hugo.garcia@outlook.com','hugo808_hashed','2024-12-15 14:00:00',0,1),(13,0,'Léa Laurent','lea.laurent@free.fr','lea909_hashed','2024-10-30 09:35:00',0,1),(14,0,'Maxime Roux','maxime.roux@hotmail.com','max1010_hashed','2024-11-25 16:15:00',0,1),(15,0,'Camille Morel','camille.morel@gmail.com','cam1111_hashed','2024-12-20 12:50:00',0,1),(16,0,'Antoine Lemoine','antoine.lemoine@yahoo.fr','ant1212_hashed','2024-10-10 18:00:00',0,1),(17,0,'Julie Fournier','julie.fournier@orange.fr','jul1313_hashed','2024-11-05 11:30:00',0,1),(18,0,'Victor Gauthier','victor.gauthier@gmail.com','vic1414_hashed','2024-12-25 10:45:00',0,1),(19,0,'Alice Mercier','alice.mercier@free.fr','ali1515_hashed','2024-10-05 13:20:00',0,1),(20,0,'Raphaël Colin','raphael.colin@hotmail.com','rap1616_hashed','2024-11-15 15:55:00',0,1),(21,0,'Azerty','azerty@gmail.com','$2y$10$qj66SoscSgvNZ6ciaMAtxOXZb3p2fhaNkNKm8OC0DzvoWocFYe8Ta','2025-04-20 13:46:13',1,1);
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-24 14:17:18
