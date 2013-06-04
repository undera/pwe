<?php

namespace PWE\Core;

use RuntimeException;

class CMDLinePWECoreTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var CMDLinePWECore
     */
    protected $object;

    public function testGetNode() {
        $this->object = new CMDLinePWECore(tempnam('/tmp', 'registry'));
        $this->object->getNode();
    }

    public function testcreate_fail() {
        try {
            $this->object = new CMDLinePWECore('');
            $this->fail();
        } catch (RuntimeException $e) {
            
        }
    }

}

