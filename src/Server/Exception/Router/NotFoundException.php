<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server\Exception\Router;

class NotFoundException extends RouterException
{
    public function __construct(\Exception $previosly = null)
    {
        parent::__construct('Not Found', 404, $previosly);
    }
}
