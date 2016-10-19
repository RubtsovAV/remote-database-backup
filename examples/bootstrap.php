<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$exporter = new \RubtsovAV\RestDatabaseExporter\Mysql([
    'host' => getenv('DB_HOST'),
    'port' => getenv('DB_PORT'),
    'username' => getenv('DB_USERNAME'),
    'password' => getenv('DB_PASSWORD'),
    'db_name' => getenv('DB_DATABASE'),
    'skip-dump-date' => true,
]);