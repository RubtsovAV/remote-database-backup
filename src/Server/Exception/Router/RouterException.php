<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server\Exception\Router;

use RubtsovAV\RemoteDatabaseBackup\Server\Exception\Exception;

class RouterException extends Exception
{
    public function __construct($responseMessage, $responseCode, \Exception $previosly = null)
    {
        parent::__construct($responseMessage, $responseCode, $previosly);
    }
}
