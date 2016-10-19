--
-- Table structure for table `table000`
--

DROP TABLE IF EXISTS `table000`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table000` (
  `id` int(11) DEFAULT NULL,
  `col01` bit(6) DEFAULT NULL,
  `col02` tinyint(4) DEFAULT NULL,
  `col03` tinyint(4) unsigned DEFAULT NULL,
  `col10` bigint(20) DEFAULT NULL,
  `col11` bigint(20) unsigned DEFAULT NULL,
  `col15` double DEFAULT NULL,
  `col27` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table000`
--

LOCK TABLES `table000` WRITE;
/*!40000 ALTER TABLE `table000` DISABLE KEYS */;
INSERT INTO `table000` VALUES 
 (1,0x21,-128,255,-9223372036854775808,18446744073709551615,-2.2250738585072014e-308,'0abcde');
/*!40000 ALTER TABLE `table000` ENABLE KEYS */;
UNLOCK TABLES;

