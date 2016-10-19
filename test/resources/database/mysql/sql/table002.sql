--
-- Table structure for table `table002`
--

DROP TABLE IF EXISTS `table002`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table002` (
  `id` int(11) DEFAULT NULL,
  `col` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table002`
--

LOCK TABLES `table002` WRITE;
/*!40000 ALTER TABLE `table002` DISABLE KEYS */;
INSERT INTO `table002` VALUES 
 (1,NULL),
 (2,-128),
 (3,0),
 (4,127);
/*!40000 ALTER TABLE `table002` ENABLE KEYS */;
UNLOCK TABLES;

