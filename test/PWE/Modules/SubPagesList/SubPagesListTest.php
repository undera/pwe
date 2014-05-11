<?php

namespace PWE\Modules\SubPagesList;

use PWE\Core\UnitTestPWECore;

require_once dirname(__FILE__) . '/../../../PWEUnitTests.php';

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
        $res = $this->object->process();
        $this->assertFalse(strstr($res, "<"));
    }

}

?>