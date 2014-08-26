<?php

namespace PWE\Modules\SubPagesList;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../../PWEUnitTests.php';

class SubPagesListTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @var SubPagesList
     */
    protected $object;

    protected function setUp() {
        
    }

    public function testGetVisitorOutput_none() {
        $PWE = new UnitTestPWECore();
        $PWE->setURL("/");
        $this->object = new SubPagesList($PWE);
        $this->object->process();
        $res=$this->object->getPWE()->getContent();
        $this->assertFalse(strstr($res, "<"));
    }

}

?>