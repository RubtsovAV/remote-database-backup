<?php

require 'bootstrap.php';

print_r($exporter->exportTable($argv[1]));