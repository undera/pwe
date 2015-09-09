<?php

namespace PWE\Lib\Doctrine;

class PWEDoctrineLoggerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PWEDoctrineLogger
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new PWEDoctrineLogger;
    }

    public function testStartQuery()
    {
        $this->object->startQuery("", array(new \DateTime()));
    }

    public function testStopQuery()
    {
        $this->object->stopQuery();
    }

}
