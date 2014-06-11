<?php

namespace PWE\Exceptions;

require_once __DIR__ . '/../../PWEUnitTests.php';

/**
 * Test class for PHPFatalException.
 * Generated by PHPUnit on 2011-09-12 at 01:32:31.
 */
class PHPFatalExceptionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var PHPFatalException
     */
    protected $object;

    protected function setUp() {
        $this->object = PHPFatalException::activate();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        restore_error_handler();
    }

    /**
     * @todo Implement test__toString().
     */
    public function test__toString() {
        try {
            $test = explode('', '');
        } catch (PHPFatalException $e) {
            echo("Caught: " . $e->__toString());
        }
    }

    /**
     * @todo Implement testErrorHandler().
     */
    public function testErrorHandler() {
        try {
            PHPFatalException::errorHandler("-1", "Test");
        } catch (PHPFatalException $e) {
            echo("Caught: " . $e->__toString());
        }
    }

}

?>
