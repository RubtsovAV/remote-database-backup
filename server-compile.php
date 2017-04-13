<?php

namespace RubtsovAV\RemoteDatabaseBackup\Compiler;

$outputFilepath = 'server-compiled.php';

$pwd = getcwd();
chdir(dirname(__FILE__));
require_once 'vendor/autoload.php';

$compiler = new Compiler();
$compiler->addTransformer(new Transformer\NamespaceToUnderscore());

$compiler->addFile('src/Server/Exception/Exception.php');
$compiler->addDir('src/Server', $exclude = [
	'src/Server/Exception/Exception.php', 
	'src/Server/init.php']
);
$compiler->addFile('src/Server/init.php');

file_put_contents($outputFilepath, $compiler->compile());

$command = 'vendor/bin/phpcbf --standard=phpcs.server-compile.xml';
exec($command);
chdir($pwd);
echo "Compiled.\n";