<?php

namespace PWE\Core;

use PWE\Modules\PWEModule;
use PWE\Modules\Outputable;
use PWE\Modules\MenuGenerator;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use \PWEUnitTests;
use PWE\Modules\PWEModulesManager;

require_once dirname(__FILE__) . '/../../PWEUnitTests.php';

class PWECoreTest extends \PHPUnit_Framework_TestCase {

    protected $object;

    protected function setUp() {
        $this->object = new PWECoreEmul();
        $this->object->setDataDirectory(dirname(__FILE__));
        $this->object->setXMLDirectory(dirname(__FILE__) . '/coreXML');
        $this->object->setTempDirectory(PWEUnitTests::utGetCleanTMP());
        $this->object->createModulesManager();
    }

    public function testSetURL_RootJTFC() {
        try {
            $this->object->setURL('/');
            throw new Exception("Exception expected");
        } catch (HTTP3xxException $e) {
            
        }
    }

    public function testSetURL_Page1Success() {
        $this->object->setURL('/test/subnode/');
    }

    public function testSetURL_Redirect() {
        try {
            $this->object->setURL('/../123/');
            throw new Exception("Exception expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/123/", $e->getMessage());
        }
    }

    public function testSetURL_AcceptParams() {
        $this->object->setURL('/accept/123/');
    }

    public function testEmptyTPL() {
        $this->object->setURL('/notpl/');
    }

    public function testSetURL_AcceptParamsFile() {
        $this->object->setURL('/accept/text.xml');
    }

    public function testSetURL_AcceptParamsFile_at_root() {
        try {
            $this->object->setURL('/robots.txt');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::NOT_FOUND) {
                throw $e;
            }
        }
    }

    public function testSetURL_AcceptParamsExceeded() {
        try {
            $this->object->setURL('/accept/123/123/123/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::BAD_REQUEST) {
                throw $e;
            }
        }
    }

    public function testSetURL_Page1FailWithParams() {
        try {
            $this->object->setURL('/test/123/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::NOT_FOUND) {
                throw $e;
            }
        }
    }

    public function testSetURL_Notexistent() {
        try {
            $this->object->setURL('/321/123/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::NOT_FOUND) {
                throw $e;
            }
        }
    }

    public function testGetModuleInstance_noParam() {
        try {
            $arr = array();
            $this->object->getModuleInstance($arr);
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            
        }
    }

    public function testGetModuleInstance_SingleInstance() {
        $mod = $this->object->getModuleInstance("PWE\\Modules\\HTMLPage\\HTMLPage");
        $this->assertTrue($mod instanceof PWEModule);
    }

    public function testGetModuleInstance_MultiInstance() {
        $node = array();
        $node['!a']['class'] = "PWE\\Modules\\HTMLPage\\HTMLPage";
        $node['!a']['src'] = "test.html";
        $mod = $this->object->getModuleInstance($node);
        $this->assertTrue($mod instanceof PWEModule);
    }

    public function testGetURL() {
        $this->object->setURL('/accept/123/');
        $res = $this->object->getURL();
    }

    public function testProcess_JTFC302() {
        $res = $this->object->process('/');
        $this->assertEquals("", $res);
    }

    public function testProcess_ok() {
        $res = $this->object->process('/test/subnode/');
        $this->assertGreaterThan(5000, strlen($res));
    }

    public function testProcess_404() {
        try {
            $res = $this->object->process('/test/subnode/404/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            
        }
    }

}

class PWECoreEmul extends PWECore {

    public function createModulesManager(PWEModulesManager $externalManager = null) {
        parent::createModulesManager($externalManager);
    }

    public function getModulesManager() {
        try {
            return parent::getModulesManager();
        } catch (\BadFunctionCallException $e) {
            return null;
        }
    }

}

class TestModule extends PWEModule implements Outputable, MenuGenerator {

    public function process() {
        
    }

    public function getMenuLevel($level) {
        
    }

}

?>