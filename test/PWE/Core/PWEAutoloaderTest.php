<?php

namespace PWE\Core;

require_once __DIR__ . '/../../PWEUnitTests.php';


class PWEAutoloaderTest extends \PHPUnit_Framework_TestCase
{

    public function testSetPWE()
    {
        PWEAutoloader::setPWE(new UnitTestPWECore());
    }

    public function testDoIt()
    {
        PWEAutoloader::setPWE(new UnitTestPWECore());
        PWEAutoloader::addSourceRoot(__DIR__);

        try {
            PWEAutoloader::doIt('PWE\Core\PWEAutoloaderNotex');
        } catch (\RuntimeException $e) {

        }

        try {
            PWEAutoloader::addSourceRoot(__DIR__ . '/notexist');
        } catch (\RuntimeException $e) {

        }

        PWEAutoloader::doIt('PWE\Core\PWEAutoloader');
        PWEAutoloader::doIt('PWE\Core\PWEAutoloader');
    }

    public function testWithUnderScores()
    {
        PWEAutoloader::doIt("OpenID_Discover");
    }

    public function test_confused()
    {
        PWEAutoloader::doIt("FindMe");
        $a = new \FindMe();
    }
}
