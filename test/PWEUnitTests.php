<?php

use PWE\Core\PWELogger;
use PWE\Core\PWEAutoloader;
use PWE\Exceptions\PHPFatalException;

error_reporting(E_ALL ^ E_NOTICE);

$src = __DIR__ . '/..';
require_once $src . '/PWE/Core/PWELogger.php';
require_once $src . '/PWE/Core/PWEAutoloader.php';

PWELogger::setStdOut('php://stdout');
PWELogger::setStdErr('php://stdout');
PWELogger::setLevel(PWELogger::DEBUG);

PWEAutoloader::addSourceRoot(__DIR__);
PWEAutoloader::addSourceRoot(__DIR__ . '/..');
PWEAutoloader::addSourceRoot(__DIR__ . '/../../vendor/');
if (is_dir(__DIR__ . '/../Lib'))
    PWEAutoloader::addSourceRoot(__DIR__ . '/../Lib');
PWEAutoloader::addSourceRoot('/usr/share/php');
PWEAutoloader::activate();

require_once(__DIR__ . '/../PWE/Utils/FilesystemHelper.php');

PHPFatalException::activate();

set_time_limit(180);

$_SERVER["DOCUMENT_ROOT"] = dirname($_SERVER["SCRIPT_FILENAME"]);

//require_once __DIR__ . '/PWE/Core/UnitTestPWECore.php';

class PWEUnitTests {

    public static function dumpArrayToFile($arr2, $toFile) {
        ob_start();
        print_r($arr2);
        file_put_contents($toFile, ob_get_contents());
        ob_end_clean();
    }

    static function utGetCleanTMP() {
        $tmpfile = tempnam("dummy", "");
        $path = dirname($tmpfile) . '/pweTest_' . time();
        if (!is_dir($path))
            mkdir($path);
        unlink($tmpfile);
        return $path;
    }

    static function getTestPWECore() {
        $pwe = new PWECore();
        $temp = PWEUnitTests::utGetCleanTMP();
        $pwe->setDataDirectory($temp);
        $pwe->setTempDirectory($temp);
        $pwe->setXMLDirectory($temp);
        PWELogger::debug("Finished creating test PWE Core");
        return $pwe;
    }

}

class ExceptionExpected extends Exception {

    public function __construct() {
        parent::__construct("Exception expected");
    }

}

?>