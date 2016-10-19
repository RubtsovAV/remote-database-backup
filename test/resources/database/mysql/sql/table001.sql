--
-- Table structure for table `table001`
--

DROP TABLE IF EXISTS `table001`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table001` (
  `id` int(11) DEFAULT NULL,
  `col` bit(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table001`
--

LOCK TABLES `table001` WRITE;
/*!40000 ALTER TABLE `table001` DISABLE KEYS */;
INSERT INTO `table001` VALUES 
 (1,NULL),
 (2,0x00),
 (3,0x01);
/*!40000 ALTER TABLE `table001` ENABLE KEYS */;
UNLOCK TABLES;

