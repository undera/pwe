<?php

namespace PWE\Exceptions;

use PWE\Core\PWELogger;

require_once __DIR__ . '/../../PWEUnitTests.php';


class HTTP5xxExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HTTP5xxException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new HTTP5xxException("test");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testInst()
    {
        PWELogger::debug("done");
    }

}
