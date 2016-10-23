<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$dir = dirname(__FILE__);

$compiler = new \RubtsovAV\RemoteDatabaseBackup\Compiler();
$compiler->addTransformer(new \RubtsovAV\RemoteDatabaseBackup\Transformer\NamespaceToUnderscore());
$compiler->addFile($dir . '/../test/resources/transformer/namespace_to_underscore_source.php');

$compiledCode = $compiler->compile();
file_put_contents($dir . '/compiled.php', $compiledCode);
echo str_replace("\r\n", "\n", $compiledCode);