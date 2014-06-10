<?php

namespace PWE\Core;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../PWEUnitTests.php';

/**
 * Test class for PWEAutoloader.
 * Generated by PHPUnit on 2011-09-12 at 02:35:12.
 */
class PWEAutoloaderTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var PWEAutoloader
     */

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @todo Implement testSetPWE().
     */
    public function testSetPWE() {
        PWEAutoloader::setPWE(new UnitTestPWECore());
    }

    /**
     * @todo Implement testDoIt().
     */
    public function testDoIt() {
        PWEAutoloader::setPWE(new UnitTestPWECore());
        PWEAutoloader::addSourceRoot(__DIR__);

        try {
            PWEAutoloader::doIt("PWE\Core\PWEAutoloaderNotex");
        } catch (\RuntimeException $e) {
            
        }

        try {
            PWEAutoloader::addSourceRoot(__DIR__ . '/notexist');
        } catch (\RuntimeException $e) {
            
        }

        PWEAutoloader::doIt("PWE\Core\PWEAutoloader");
        PWEAutoloader::doIt("PWE\Core\PWEAutoloader");
    }

    public function testWithUnderScores() {
        PWEAutoloader::doIt("OpenID_Discover");
    }

}

?>
