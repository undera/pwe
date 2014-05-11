<?php

namespace PWE\Modules\HTMLPage;

use PWE\Core\UnitTestPWECore;
use PWE\Exceptions\HTTP4xxException;

require_once dirname(__FILE__) . '/../../../PWEUnitTests.php';

class HTMLPageTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @var UnitTestPWECore
     */
    private $PWE;
    protected $object;

    protected function setUp() {
        $this->PWE = new UnitTestPWECore();
        $tmp = $this->PWE->getTempDirectory();
        $this->PWE->setDataDirectory(dirname(__FILE__));
        $this->PWE->setTempDirectory($tmp);
        $this->PWE->setStructFile(dirname(__FILE__) . '/HTMLPage.xml');
    }

    public function testGetVisitorOutput_UnderConstr() {
        $this->PWE->setURL("/");
        $this->object = new HTMLPage($this->PWE);

        try {
            $this->object->process();
            $this->fail();
        } catch (HTTP4xxException $e) {
            
        }
    }

    public function testGetVisitorOutput_UnderConstr2() {
        $this->PWE->setURL("/notready/");
        $this->object = new HTMLPage($this->PWE);

        try {
            $this->object->process();
            $this->fail();
        } catch (HTTP4xxException $e) {
            
        }
    }

    public function testGetVisitorOutput_UnderConstr3() {
        $this->PWE->setURL("/nothtml/");
        $this->object = new HTMLPage($this->PWE);

        try {
            $this->object->process();
            $this->fail();
        } catch (HTTP4xxException $e) {
            
        }
    }

    public function testGetVisitorOutput_OK() {
        $this->PWE->setURL("/htmltest/");
        $this->object = new HTMLPage($this->PWE);
        $res = $this->object->process();
        $this->assertEquals("<html><head><title></title></head><body>TEST</body></html>", $this->PWE->getContent());
    }

    public function testSetup() {
        $this->PWE->setDataDirectory($this->PWE->getTempDirectory());
        $registerData = array();
        HTMLPage::setup($this->PWE, $registerData);
    }

}

?>