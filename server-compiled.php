<?php
class RubtsovAV_RestDatabaseExporter_Server_Router
{
    public function route($requestData)
    {
        header('Content-Type: text/plain;charset=UTF-8');
        try {
            $adapter = $this->createAdapter($requestData);
            $action = $this->parseAction($requestData);

            if (!method_exists($adapter, $action['name'])) {
                throw new RubtsovAV_RestDatabaseExporter_Server_Exception_Router_NotFoundException();
            }
            $response = call_user_func_array(array($adapter, $action['name']), $action['data']);
        } catch (RouterException $ex) {
            $responseCode = $ex->getCode();
            $responseMessage = $ex->getMessage();
            header("HTTP/1.1 $responseCode $responseMessage");
        } catch (Exception $ex) {
            header('HTTP/1.1 417 Expectation failed');
            $response = array(
                'error' => array(
                    'message' => $ex->getMessage(),
                    'code' => $ex->getCode(),
                    'trace' => $ex->getTrace(),
                )
            );
        }

        if (is_array($response)) {
            header('Content-Type: application/json');
            $response = json_encode($response);
        } 

        if (isset($requestData['response_mark'])) {
            $response .= $requestData['response_mark'];
        }        
        return $response;
    }

    public function createAdapter($requestData)
    {
        $adapterName = null;
        if (isset($requestData['adapter']) && is_string($requestData['adapter'])) {
            $adapterName = $requestData['adapter'];
        }

        $adapterParams = array();
        if (isset($requestData['db']) && is_array($requestData['db'])) {
            $adapterParams = $requestData['db'];
        }

        $adapterFactory = new RubtsovAV_RestDatabaseExporter_Server_DatabaseAdapterFactory();
        return $adapterFactory->createAdapter($adapterName, $adapterParams);
    }

    public function parseAction($requestData)
    {
        $actionName = null;
        if (isset($requestData['action']) && is_string($requestData['action'])) {
            $actionName = $requestData['action'];
        }

        $actionData = array();
        if (isset($requestData['data']) && is_array($requestData['data'])) {
            $actionData = $requestData['data'];
        }

        return array(
            'name' => $actionName,
            'data' => $actionData,
        );
    }
}
class RubtsovAV_RestDatabaseExporter_Server_DatabaseAdapterFactory
{
	public function createAdapter($name = null, $params = array())
	{
		if (!$name) {
			$name = $this->getAvailableAdapters();
			$name = array_shift($name);
		}

		$name = strtolower($name);
		if (!$this->adapterIsAvailable($name)) {
			throw new Exception("adapter '$name' is not available");
		}
		
		switch ($name)
		{
			case 'mysqli':
				return new RubtsovAV_RestDatabaseExporter_Server_DatabaseAdapter_Mysqli($params);
		}
	}

	public function adapterIsAvailable($name)
	{
		return in_array($name, $this->getAvailableAdapters());
	}

	public function getAvailableAdapters()
	{
		$availableAdapters = array();
		if (function_exists('mysqli_connect')) {
			$availableAdapters[] = 'mysqli';
		}

		return $availableAdapters;
	}
}
class RubtsovAV_RestDatabaseExporter_Server_Exception_Router_RouterException extends RubtsovAV_RestDatabaseExporter_Server_Exception_Exception
{
    public function __construct($responseMessage, $responseCode, Exception $previosly = null)
    {
        parent::__construct($responseMessage, $responseCode, $previosly);
    }
}
class RubtsovAV_RestDatabaseExporter_Server_Exception_Router_NotFoundException extends RubtsovAV_RestDatabaseExporter_Server_Exception_Router_RouterException
{
    public function __construct(Exception $previosly = null)
    {
        parent::__construct('Not Found', 404, $previosly);
    }
}
class RubtsovAV_RestDatabaseExporter_Server_Exception_Exception extends Exception
{

}
class RubtsovAV_RestDatabaseExporter_Server_Exception_DatabaseAdapter_DatabaseAdapter extends RubtsovAV_RestDatabaseExporter_Server_Exception_Exception
{
    public function __construct($message = '', $code = 0, Exception $previosly = null)
    {
        parent::__construct($message, $code, $previosly);
    }
}
class RubtsovAV_RestDatabaseExporter_Server_Exception_DatabaseAdapter_AdapterNotAvailable extends RubtsovAV_RestDatabaseExporter_Server_Exception_Exception
{
    public function __construct($adapterName, Exception $previosly = null)
    {
        parent::__construct("adapter '$adapterName' not available", 0, $previosly);
    }
}
interface RubtsovAV_RestDatabaseExporter_Server_DatabaseAdapter_AdapterInterface
{
    public function getDatabaseMetadata();
    public function getTablesMetadata();
    public function exportHeader();
    public function exportFooter();
    public function exportCreateDatabase();
    public function exportTable($tableName);
    public function exportViews();
    public function exportTriggers();
    public function exportRoutines();
}
class RubtsovAV_RestDatabaseExporter_Server_DatabaseAdapter_Mysqli implements RubtsovAV_RestDatabaseExporter_Server_DatabaseAdapter_AdapterInterface
{
    // Same as mysqldump
    const MAXLINESIZE = 1000000;

    // Available connection strings
    const UTF8 = 'utf8';
    const UTF8MB4 = 'utf8mb4';

    private $config = array();

    // Numerical Mysql types
    private $mysqlTypes = array(
        'numerical' => array(
            'bit',
            'tinyint',
            'smallint',
            'mediumint',
            'int',
            'integer',
            'bigint',
            'real',
            'double',
            'float',
            'decimal',
            'numeric'
        ),
        'blob' => array(
            'tinyblob',
            'blob',
            'mediumblob',
            'longblob',
            'binary',
            'varbinary',
            'bit',
            'geometry', /* http://bugs.mysql.com/bug.php?id=43544 */
            'point',
            'linestring',
            'polygon',
            'multipoint',
            'multilinestring',
            'multipolygon',
            'geometrycollection',
        )
    );

    public function __construct($config = array())
    {
        $this->config = $config;
        $this->defaultCharacterSet = self::UTF8;

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->db = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['db_name'],
            $config['port']
        );

        $this->db->query("SET NAMES {$this->defaultCharacterSet}");
        $this->db->query("SET TIME_ZONE='+00:00'");

        $this->db->set_charset('utf8');

        $output = $config['output'];
        if (!$output) {
            $output = 'php://output';
        }

        $this->outputResource = fopen($output, 'r+');
    }

    public function __destruct()
    {
        fclose($this->outputResource);
        $this->db->close();
    }

    public function getDatabaseMetadata()
    {
        $sql = 'SELECT SCHEMA_NAME as db_name, ' .
                'DEFAULT_CHARACTER_SET_NAME as default_character_set_name, ' .
                'DEFAULT_COLLATION_NAME as default_collation_name ' .
                "FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = 'rest_database_exporter'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        $result->close();
        return $row;
    }

    public function getTablesMetadata()
    {
        $sql = 'SELECT TABLE_NAME AS name ' .
            'FROM INFORMATION_SCHEMA.TABLES ' .
            "WHERE TABLE_TYPE='BASE TABLE' AND TABLE_SCHEMA='{$this->config['db_name']}'";

        $result = $this->db->query($sql);
        $tablesMetadata = array();
        while ($row = $result->fetch_assoc()) {
            $tablesMetadata[] = $row;
        }
        $result->close();
        return $tablesMetadata;
    }

    public function exportHeader()
    {
        $sql = 'SELECT version();';
        $result = $this->db->query($sql);
        $row = $result->fetch_array();
        $result->close();
        $dbVersion = $row[0];

        $header = '--' . "\n" .
                "-- Host: {$this->config['host']}\tDatabase: {$this->config['db_name']}" . "\n" .
                '-- ------------------------------------------------------' . "\n";

        $header .= "-- Server version \t" . $dbVersion . "\n";

        if (!$this->config['skip-dump-date']) {
            $header .= '-- Date: ' . date('r') . "\n";
        }

        $header .= "\n";

        $header .= '/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;' . "\n" .
                    '/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;' . "\n" .
                    '/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;' . "\n" .
                    '/*!40101 SET NAMES utf8 */;' . "\n" .
                    '/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;' . "\n" .
                    "/*!40103 SET TIME_ZONE='+00:00' */;" . "\n" .
                    '/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;' . "\n" .
                    '/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;' . "\n" .
                    "/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;" . "\n" .
                    '/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;' . "\n";

        $header .= "\n";
        
        $this->output($header);
        unset($header);
    }

    public function exportFooter()
    {
        $footer = '/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;' . "\n" . "\n";

        $footer .= '/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;' . "\n" .
            '/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;' . "\n" .
            '/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;' . "\n" .
            '/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;' . "\n" .
            '/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;' . "\n" .
            '/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;' . "\n" .
            '/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;' . "\n" . "\n";

        $footer .= '-- Dump completed';
        if (!$this->config['skip-dump-date']) {
            $footer .= ' on: ' . date('r');
        }
        $footer .= "\n" . "\n";
        
        $this->output($footer);
        unset($footer);
    }

    public function exportCreateDatabase()
    {
        $dbInfo = $this->getDatabaseMetadata();
        $ret .= '--' . "\n" .
                "-- Current Database: `{$dbInfo['db_name']}`" . "\n" .
                '--' . "\n" . "\n";

        $ret .= "/*!40000 DROP DATABASE IF EXISTS `{$dbInfo['db_name']}`*/;" . "\n" . "\n";

        $ret .= "CREATE DATABASE /*!32312 IF NOT EXISTS*/ `{$dbInfo['db_name']}` " .
                "/*!40100 DEFAULT CHARACTER SET {$dbInfo['default_character_set_name']} */;" .
                "\n" . "\n";
                
        $ret .= "USE `{$dbInfo['db_name']}`;" . "\n" . "\n";

        $this->output($ret);
        unset($ret);
    }

    public function exportTable($tableName)
    {
        $sql = "SHOW CREATE TABLE `$tableName`";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        $result->close();

        $ret = '--' . "\n" .
               "-- Table structure for table `$tableName`" . "\n" .
               '--' . "\n" . "\n";

        $ret .= "DROP TABLE IF EXISTS `$tableName`;" . "\n";
        $ret .= '/*!40101 SET @saved_cs_client     = @@character_set_client */;' . "\n" .
            '/*!40101 SET character_set_client = ' . $this->defaultCharacterSet . ' */;' . "\n" .
            $row['Create Table'] . ';' . "\n" .
            '/*!40101 SET character_set_client = @saved_cs_client */;' . "\n" .
            "\n";

        $ret .= '--' . "\n" .
                "-- Dumping data for table `$tableName`" .  "\n" .
                '--' . "\n" . "\n";

        // Prepare to get data
        $sql = 'SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ';
        $this->db->query($sql);
        $sql = 'START TRANSACTION';
        $this->db->query($sql);
        $sql = "LOCK TABLES `$tableName` READ LOCAL";
        $this->db->query($sql);

        $ret .= "LOCK TABLES `$tableName` WRITE;" . "\n";
        $ret .= "/*!40000 ALTER TABLE `$tableName` DISABLE KEYS */;" . "\n";

        $this->output($ret);
        unset($ret);

        $tableColumnTypes = $this->getTableColumnTypes($tableName);
        $colStmt = $this->getColumnStmt($tableColumnTypes);

        $stmt = "SELECT $colStmt FROM `$tableName`";
        $result = $this->db->query($stmt, MYSQLI_USE_RESULT);

        $onlyOnce = true;
        $lineSize = 0;
        while ($row = $result->fetch_assoc()) {
            $vals = $this->escape($tableName, $row, $tableColumnTypes);
            if ($onlyOnce) {
                $lineSize = $this->output("INSERT INTO `$tableName` VALUES \n (" . implode(',', $vals) . ')');
                $onlyOnce = false;
            } else {
                $lineSize += $this->output(",\n (" . implode(',', $vals) . ')');
            }
            if ($lineSize > self::MAXLINESIZE) {
                $onlyOnce = true;
                $this->output(';' . "\n");
            }
        }
        $result->close();

        if (!$onlyOnce) {
            $this->output(';' . "\n");
        }

        $ret = "/*!40000 ALTER TABLE `$tableName` ENABLE KEYS */;" . "\n";
        $ret .= 'UNLOCK TABLES;' . "\n";
        $ret .= "\n";

        $this->output($ret);
        unset($ret);

        $this->db->query('COMMIT');
        $this->db->query('UNLOCK TABLES');
    }

    public function exportViews()
    {
        $sql = 'SELECT TABLE_NAME AS name ' .
            'FROM INFORMATION_SCHEMA.TABLES ' .
            "WHERE TABLE_TYPE='VIEW' AND TABLE_SCHEMA='{$this->config['db_name']}'";
        $result = $this->db->query($sql);
        $views = array();
        while ($row = $result->fetch_assoc()) {
            $views[] = $row['name'];
        }
        $result->close();

        $ret = '';
        foreach ($views as $viewName) {
            $ret .= '--' . "\n" .
                "-- Temporary table structure for view `${viewName}`" . "\n" .
                '--' . "\n" . "\n";

            $ret .= "DROP TABLE IF EXISTS `$viewName`;" . "\n" .
                    "/*!50001 DROP VIEW IF EXISTS `$viewName`*/;" . "\n";

            $tableColumnTypes = $this->getTableColumnTypes($viewName);
            $colStmt = '';
            foreach ($tableColumnTypes as $k => $v) {
                $colStmt .= "  `${k}` tinyint NOT NULL," . "\n";
            }
            if (!empty($colStmt)) {
                $colStmt = $this->substr($colStmt, 0, $this->strlen($colStmt) - 2);
            }

            $ret .= 'SET @saved_cs_client     = @@character_set_client;' . "\n" .
                    "SET character_set_client = {$this->defaultCharacterSet};" . "\n";
            $ret .= "/*!50001 CREATE TABLE `$viewName` (" .
                "\n" . $colStmt . "\n" . ') ENGINE=MyISAM */;' . "\n";
            $ret .= 'SET character_set_client = @saved_cs_client;' . "\n" . "\n";
        }

        foreach ($views as $viewName) {
            $ret .= '--' . "\n" .
                "-- Final view structure for view `${viewName}`" . "\n" .
                '--' . "\n" . "\n";

            $sql = "SHOW CREATE VIEW `$viewName`";
            $result = $this->db->query($sql);
            $row = $result->fetch_assoc();
            $result->close();

            $triggerStmt = $row['Create View'];

            $triggerStmtReplaced1 = str_replace(
                'CREATE ALGORITHM',
                '/*!50001 CREATE ALGORITHM',
                $triggerStmt
            );
            $triggerStmtReplaced2 = str_replace(
                ' DEFINER=',
                ' */' . "\n" . '/*!50013 DEFINER=',
                $triggerStmtReplaced1
            );
            $triggerStmtReplaced3 = str_replace(
                ' VIEW ',
                ' */' . "\n" . '/*!50001 VIEW ',
                $triggerStmtReplaced2
            );
            if (false === $triggerStmtReplaced1 ||
                false === $triggerStmtReplaced2 ||
                false === $triggerStmtReplaced3) {
                $triggerStmtReplaced = $triggerStmt;
            } else {
                $triggerStmtReplaced = $triggerStmtReplaced3 . ' */;';
            }

            $ret .= "/*!50001 DROP TABLE IF EXISTS `$viewName`*/;" . "\n" .
                "/*!50001 DROP VIEW IF EXISTS `$viewName`*/;" . "\n";
            
            $ret .= '/*!50001 SET @saved_cs_client          = @@character_set_client */;' . "\n" .
                '/*!50001 SET @saved_cs_results         = @@character_set_results */;' . "\n" .
                '/*!50001 SET @saved_col_connection     = @@collation_connection */;' . "\n" .
                '/*!50001 SET character_set_client      = latin1 */;' . "\n" .
                '/*!50001 SET character_set_results     = latin1 */;' . "\n" .
                '/*!50001 SET collation_connection      = latin1_swedish_ci */;' . "\n";

            $ret .= $triggerStmtReplaced . "\n";

            $ret .= '/*!50001 SET character_set_client      = @saved_cs_client */;' . "\n" .
                    '/*!50001 SET character_set_results     = @saved_cs_results */;' . "\n" .
                    '/*!50001 SET collation_connection      = @saved_col_connection */;' . "\n";
            $ret .= "\n";
        }

        $this->output($ret);
        unset($ret);
    }

    public function exportTriggers()
    {
        $dbInfo = $this->getDatabaseMetadata($this->config['db_name']);

        $sql = "SHOW TRIGGERS FROM `{$this->config['db_name']}`;";
        $result = $this->db->query($sql);
        $triggers = array();
        while ($row = $result->fetch_assoc()) {
            $triggers[] = $row['Trigger'];
        }
        $result->close();

        $ret = '';
        foreach ($triggers as $triggerName) {
            $sql = "SHOW CREATE TRIGGER `$triggerName`";
            $result = $this->db->query($sql);
            $row = $result->fetch_assoc();
            $result->close();

            $ret .= "/*!50032 DROP TRIGGER IF EXISTS `$triggerName` */;" . "\n";

            $triggerStmt = $row['SQL Original Statement'];
            $triggerStmtReplaced = str_replace(
                'CREATE DEFINER',
                '/*!50003 CREATE*/ /*!50017 DEFINER',
                $triggerStmt
            );
            $triggerStmtReplaced = str_replace(
                ' TRIGGER',
                '*/ /*!50003 TRIGGER',
                $triggerStmtReplaced
            );
            if (false === $triggerStmtReplaced) {
                $triggerStmtReplaced = $triggerStmt;
            }

            $ret .= "ALTER DATABASE `{$this->config['db_name']}` " .
                    'CHARACTER SET latin1 COLLATE latin1_swedish_ci ;' . "\n";

            $ret .= '/*!50003 SET @saved_cs_client      = @@character_set_client */ ;' . "\n" .
                '/*!50003 SET @saved_cs_results     = @@character_set_results */ ;' . "\n" .
                '/*!50003 SET @saved_col_connection = @@collation_connection */ ;' . "\n" .
                '/*!50003 SET character_set_client  = latin1 */ ;' . "\n" .
                '/*!50003 SET character_set_results = latin1 */ ;' . "\n" .
                '/*!50003 SET collation_connection  = latin1_swedish_ci */ ;' . "\n" .
                '/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;' . "\n" .
                "/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;" . "\n";

            $ret .= 'DELIMITER ;;' . "\n" .
                $triggerStmtReplaced . ' */;;' . "\n" .
                'DELIMITER ;' . "\n";

            $ret .= '/*!50003 SET sql_mode              = @saved_sql_mode */ ;' . "\n" .
                    '/*!50003 SET character_set_client  = @saved_cs_client */ ;' . "\n" .
                    '/*!50003 SET character_set_results = @saved_cs_results */ ;' . "\n" .
                    '/*!50003 SET collation_connection  = @saved_col_connection */ ;' . "\n";

            $ret .= "ALTER DATABASE `{$this->config['db_name']}` ".
                    "CHARACTER SET {$dbInfo['default_character_set_name']} ".
                    "COLLATE {$dbInfo['default_collation_name']} ;" . "\n";

            $ret .= "\n";
        }

        $this->output($ret);
        unset($ret);
    }

    public function exportRoutines()
    {
        $sql = 'SELECT SPECIFIC_NAME AS procedure_name ' .
            'FROM INFORMATION_SCHEMA.ROUTINES ' .
            "WHERE ROUTINE_TYPE='PROCEDURE' AND ROUTINE_SCHEMA='{$this->config['db_name']}'";
        $result = $this->db->query($sql);
        $procedures = array();
        while ($row = $result->fetch_assoc()) {
            $procedures[] = $row['procedure_name'];
        }
        $result->close();

        $ret = '--' . "\n" .
                "-- Dumping routines for database '" . $this->config['db_name'] . "'" . "\n" .
                '--' . "\n";

        foreach ($procedures as $procedureName) {
            $sql = "SHOW CREATE PROCEDURE `$procedureName`";
            $result = $this->db->query($sql);
            $row = $result->fetch_assoc();
            $result->close();

            $procedureStmt = $row['Create Procedure'];

            $ret .= '/*!50003 DROP PROCEDURE IF EXISTS `' . $row['Procedure'] . '` */;' . "\n";

            $ret .= '/*!50003 SET @saved_cs_client      = @@character_set_client */ ;' . "\n" .
                    '/*!50003 SET @saved_cs_results     = @@character_set_results */ ;' . "\n" .
                    '/*!50003 SET @saved_col_connection = @@collation_connection */ ;' . "\n" .
                    '/*!50003 SET character_set_client  = latin1 */ ;' . "\n" .
                    '/*!50003 SET character_set_results = latin1 */ ;' . "\n" .
                    '/*!50003 SET collation_connection  = latin1_swedish_ci */ ;' . "\n" .
                    '/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;' . "\n" .
                    "/*!50003 SET sql_mode              = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;" . "\n";

            $ret .= 'DELIMITER ;;' . "\n" .
                $procedureStmt . ' ;;' . "\n" .
                'DELIMITER ;' . "\n";

            $ret .= '/*!50003 SET sql_mode              = @saved_sql_mode */ ;' . "\n" .
                    '/*!50003 SET character_set_client  = @saved_cs_client */ ;' . "\n" .
                    '/*!50003 SET character_set_results = @saved_cs_results */ ;' . "\n" .
                    '/*!50003 SET collation_connection  = @saved_col_connection */ ;' . "\n";
            $ret .= "\n";
        }
        
        $this->output($ret);
        unset($ret);
    }

    /**
     * Build SQL List of all columns on current table
     *
     * @return string SQL sentence with columns
     */
    private function getColumnStmt($tableColumnTypes)
    {
        $colStmt = array();
        foreach ($tableColumnTypes as $colName => $colType) {
            if ($colType['type'] == 'bit') {
                $colStmt[] = "LPAD(HEX(`${colName}`),2,'0') AS `${colName}`";
            } elseif ($colType['is_blob']) {
                $colStmt[] = "HEX(`${colName}`) AS `${colName}`";
            } else {
                $colStmt[] = "`${colName}`";
            }
        }
        $colStmt = implode($colStmt, ',');

        return $colStmt;
    }

    /**
     * Store column types to create data dumps and for Stand-In tables
     *
     * @param string $tableName  Name of table to export
     * @return array type column types detailed
     */

    private function getTableColumnTypes($tableName)
    {
        $columnTypes = array();
        $result = $this->db->query("SHOW COLUMNS FROM `$tableName`;");
        while ($col = $result->fetch_assoc()) {
            $types = $this->parseColumnType($col);
            $columnTypes[$col['Field']] = array(
                'is_numeric'=> $types['is_numeric'],
                'is_blob' => $types['is_blob'],
                'type' => $types['type'],
                'type_sql' => $col['Type']
            );
        }
        $result->close();
        return $columnTypes;
    }

    /**
     * Decode column metadata and fill info structure.
     * type, is_numeric and is_blob will always be available.
     *
     * @param array $colType Array returned from "SHOW COLUMNS FROM tableName"
     * @return array
     */
    public function parseColumnType($colType)
    {
        $colInfo = array();
        $colParts = explode(' ', $colType['Type']);

        if ($fparen = strpos($colParts[0], '(')) {
            $colInfo['type'] = substr($colParts[0], 0, $fparen);
            $colInfo['length']  = str_replace(')', '', substr($colParts[0], $fparen+1));
            $colInfo['attributes'] = isset($colParts[1]) ? $colParts[1] : null;
        } else {
            $colInfo['type'] = $colParts[0];
        }
        $colInfo['is_numeric'] = in_array($colInfo['type'], $this->mysqlTypes['numerical']);
        $colInfo['is_blob'] = in_array($colInfo['type'], $this->mysqlTypes['blob']);

        return $colInfo;
    }

    /**
     * Escape values with quotes when needed
     *
     * @param string $tableName Name of table which contains rows
     * @param array $row Associative array of column names and values to be quoted
     *
     * @return string
     */
    private function escape($tableName, $row, $columnTypes)
    {
        $ret = array();
        foreach ($row as $colName => $colValue) {
            if (is_null($colValue)) {
                $ret[] = 'NULL';
            } elseif ($columnTypes[$colName]['is_blob']) {
                if ($columnTypes[$colName]['type'] == 'bit' || !empty($colValue)) {
                    $ret[] = "0x${colValue}";
                } else {
                    $ret[] = "''";
                }
            } elseif ($columnTypes[$colName]['is_numeric']) {
                $ret[] = $colValue;
            } else {
                $ret[] = "'" . $this->db->real_escape_string($colValue) . "'";
            }
        }
        return $ret;
    }

    private function output($string)
    {
        return fwrite($this->outputResource, $string);
    }

    private function strlen($s)
    {
        if (function_exists('mb_orig_strlen')) {
            return mb_orig_strlen($s);
        }
        return strlen($s);
    }

    private function substr($s, $from, $length)
    {
        if (function_exists('mb_orig_substr')) {
            return mb_orig_substr($s, $from, $length);
        }
        return substr($s, $from, $length);
    }
}
ini_set('display_errors', false);

$router = new RubtsovAV_RestDatabaseExporter_Server_Router();
echo $router->route($_POST);
