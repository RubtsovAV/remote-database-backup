--
-- Table structure for table `table027`
--

DROP TABLE IF EXISTS `table027`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `table027` (
  `id` int(11) DEFAULT NULL,
  `col` varchar(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `table027`
--

LOCK TABLES `table027` WRITE;
/*!40000 ALTER TABLE `table027` DISABLE KEYS */;
INSERT INTO `table027` VALUES 
 (1,NULL),
 (2,''),
 (3,'0'),
 (4,'2e308'),
 (5,'999.99'),
 (6,'-2e-30'),
 (7,'-99.99'),
 (8,'0'),
 (9,'0abcde'),
 (10,'123');
/*!40000 ALTER TABLE `table027` ENABLE KEYS */;
UNLOCK TABLES;

