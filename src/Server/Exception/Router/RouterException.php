<?php

namespace RubtsovAV\RestDatabaseExporter\Server\Exception\Router;

use RubtsovAV\RestDatabaseExporter\Server\Exception\Exception;

class RouterException extends Exception
{
    public function __construct($responseMessage, $responseCode, \Exception $previosly = null)
    {
        parent::__construct($responseMessage, $responseCode, $previosly);
    }
}
