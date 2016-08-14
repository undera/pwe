<?php

namespace PWE\Exceptions;

require_once __DIR__ . '/../../PWEUnitTests.php';


class HTTP2xxExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var HTTP2xxException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new HTTP2xxException("hello");
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @todo Implement testIsUsingTemplate().
     */
    public function testIsUsingTemplate()
    {
        $this->object->isUsingTemplate();
    }

    /**
     * @todo Implement testSetUsingTemplate().
     */
    public function testSetUsingTemplate()
    {
        $this->object->setUsingTemplate(true);
    }

}
