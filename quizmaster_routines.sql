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
-- Temporary view structure for view `duel_leaderboard`
--

DROP TABLE IF EXISTS `duel_leaderboard`;
/*!50001 DROP VIEW IF EXISTS `duel_leaderboard`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `duel_leaderboard` AS SELECT 
 1 AS `id`,
 1 AS `nom`,
 1 AS `est_contributeur`,
 1 AS `total_duels`,
 1 AS `wins`,
 1 AS `losses`,
 1 AS `draws`,
 1 AS `win_percentage`,
 1 AS `avg_accuracy`,
 1 AS `avg_completion_time`,
 1 AS `last_duel_date`,
 1 AS `preferred_duel_type`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `duel_leaderboard`
--

/*!50001 DROP VIEW IF EXISTS `duel_leaderboard`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `duel_leaderboard` AS select `u`.`id` AS `id`,`u`.`nom` AS `nom`,`u`.`est_contributeur` AS `est_contributeur`,count(`d`.`id`) AS `total_duels`,sum((case when (`d`.`winner_id` = `u`.`id`) then 1 else 0 end)) AS `wins`,sum((case when ((`d`.`winner_id` is not null) and (`d`.`winner_id` <> `u`.`id`) and (`d`.`winner_id` <> 0)) then 1 else 0 end)) AS `losses`,sum((case when (`d`.`winner_id` = 0) then 1 else 0 end)) AS `draws`,((sum((case when (`d`.`winner_id` = `u`.`id`) then 1 else 0 end)) / count(`d`.`id`)) * 100) AS `win_percentage`,avg((case when (`dr`.`total_questions` > 0) then ((`dr`.`correct_answers` / `dr`.`total_questions`) * 100) else 0 end)) AS `avg_accuracy`,avg(`dr`.`completion_time`) AS `avg_completion_time`,max(`d`.`completed_at`) AS `last_duel_date`,(select `duels`.`type` from `duels` where ((`duels`.`challenger_id` = `u`.`id`) or (`duels`.`opponent_id` = `u`.`id`)) group by `duels`.`type` order by count(0) desc limit 1) AS `preferred_duel_type` from ((`utilisateurs` `u` left join `duels` `d` on((((`d`.`challenger_id` = `u`.`id`) or (`d`.`opponent_id` = `u`.`id`)) and (`d`.`status` = 'completed')))) left join `duel_results` `dr` on(((`dr`.`user_id` = `u`.`id`) and (`dr`.`duel_id` = `d`.`id`)))) group by `u`.`id`,`u`.`nom`,`u`.`est_contributeur` having (`total_duels` > 0) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-24 14:17:20
