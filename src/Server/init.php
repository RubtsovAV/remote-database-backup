<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server;

ini_set('display_errors', false);

$router = new Router();
echo $router->route($_POST);
