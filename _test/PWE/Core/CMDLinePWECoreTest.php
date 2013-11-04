<?php

namespace PWE\Core;

class CMDLinePWECoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CMDLinePWECore
     */
    protected $object;

    public function testGetNode()
    {
        $this->object = new CMDLinePWECore(tempnam('/tmp', 'registry'));
        $this->object->getNode();
    }

}

