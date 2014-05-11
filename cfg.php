<?php

use PWE\Core\PWELogger;
use PWE\Core\PWEAutoloader;

header('Server: Apache');
header('X-Powered-By: PWE');

$root = str_replace('\\', '/', dirname(__FILE__));

PWELogger::setLevel(PWELogger::WARNING);

$PWECore->setStaticDirectory($root . '/img');
$PWECore->setStaticHref('/img');

$PWECore->setDataDirectory($root . '/dat');
$PWECore->setTempDirectory($root . '/dat/tmp');
PWEAutoloader::addSourceRoot($root . '/PWE');
?>