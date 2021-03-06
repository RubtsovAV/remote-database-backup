<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server\Exception\DatabaseAdapter;

use RubtsovAV\RemoteDatabaseBackup\Server\Exception\Exception;

class DatabaseAdapter extends Exception
{
    public function __construct($message = '', $code = 0, \Exception $previosly = null)
    {
        parent::__construct($message, $code, $previosly);
    }
}
