--
-- Temporary table structure for view `table100`
--

DROP TABLE IF EXISTS `table100`;
/*!50001 DROP VIEW IF EXISTS `table100`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `table100` (
  `id` tinyint NOT NULL,
  `col01` tinyint NOT NULL,
  `col02` tinyint NOT NULL,
  `col03` tinyint NOT NULL,
  `col10` tinyint NOT NULL,
  `col11` tinyint NOT NULL,
  `col15` tinyint NOT NULL,
  `col27` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `table127`
--

DROP TABLE IF EXISTS `table127`;
/*!50001 DROP VIEW IF EXISTS `table127`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE TABLE `table127` (
  `id` tinyint NOT NULL,
  `col` tinyint NOT NULL
) ENGINE=MyISAM */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `table100`
--

/*!50001 DROP TABLE IF EXISTS `table100`*/;
/*!50001 DROP VIEW IF EXISTS `table100`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `table100` AS select `table000`.`id` AS `id`,`table000`.`col01` AS `col01`,`table000`.`col02` AS `col02`,`table000`.`col03` AS `col03`,`table000`.`col10` AS `col10`,`table000`.`col11` AS `col11`,`table000`.`col15` AS `col15`,`table000`.`col27` AS `col27` from `table000` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `table127`
--

/*!50001 DROP TABLE IF EXISTS `table127`*/;
/*!50001 DROP VIEW IF EXISTS `table127`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = latin1 */;
/*!50001 SET character_set_results     = latin1 */;
/*!50001 SET collation_connection      = latin1_swedish_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `table127` AS select `table027`.`id` AS `id`,`table027`.`col` AS `col` from `table027` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

