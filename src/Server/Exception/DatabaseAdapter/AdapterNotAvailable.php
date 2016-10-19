<?php

namespace RubtsovAV\RestDatabaseExporter\Server\Exception\DatabaseAdapter;

use RubtsovAV\RestDatabaseExporter\Server\Exception\Exception;

class AdapterNotAvailable extends Exception
{
    public function __construct($adapterName, \Exception $previosly = null)
    {
        parent::__construct("adapter '$adapterName' not available", 0, $previosly);
    }
}
