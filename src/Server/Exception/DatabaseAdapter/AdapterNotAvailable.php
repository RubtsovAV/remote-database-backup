<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server\Exception\DatabaseAdapter;

use RubtsovAV\RemoteDatabaseBackup\Server\Exception\Exception;

class AdapterNotAvailable extends Exception
{
    public function __construct($adapterName, \Exception $previosly = null)
    {
        parent::__construct("adapter '$adapterName' not available", 0, $previosly);
    }
}
