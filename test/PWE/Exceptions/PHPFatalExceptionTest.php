<?php

namespace PWE\Exceptions;

require_once __DIR__ . '/../../PWEUnitTests.php';


class PHPFatalExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PHPFatalException
     */
    protected $object;

    protected function setUp()
    {
        $this->object = PHPFatalException::activate();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        restore_error_handler();
    }

    /**
     * @todo Implement test__toString().
     */
    public function test__toString()
    {
        try {
            explode('', '');
        } catch (PHPFatalException $e) {
            echo("Caught: " . $e->__toString());
        }
    }

    /**
     * @todo Implement testErrorHandler().
     */
    public function testErrorHandler()
    {
        try {
            /** @noinspection PhpParamsInspection */
            PHPFatalException::errorHandler("-1", "Test"); # intended to be wrong
        } catch (PHPFatalException $e) {
            echo("Caught: " . $e->__toString());
        }
    }

}
