<?php

namespace PWE\Core;

use BadFunctionCallException;
use InvalidArgumentException;
use PWE\Modules\PWEModule;
use PWE\Modules\PWEModulesManager;

require_once __DIR__ . '/../../PWEUnitTests.php';

class AbstractPWECoreTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var AbstractPWECore
     */
    protected $object;

    protected function setUp() {
        $this->object = new AbstractPWECoreImpl();
        $this->object->createModulesManager();
    }

    protected function tearDown() {
        
    }

    public function testSetRootDirectory() {
        $this->object->setRootDirectory('');
    }

    public function testSetDataDirectory() {
        $this->object->setDataDirectory('');
    }

    public function testSetXMLDirectory() {
        $this->object->setXMLDirectory('');
    }

    public function testSetTempDirectory() {
        $this->object->setTempDirectory('');
    }

    public function testGetDataDirectory() {
        $this->object->getDataDirectory();
    }

    public function testGetXMLDirectory() {
        $this->object->getDataDirectory();
    }

    public function testGetTempDirectory() {
        $this->object->getTempDirectory();
    }

    public function testGetModuleInstance_noParam()
    {
        try {
            $arr = array();
            $this->object->getModuleInstance($arr);
            $this->fail();
        } catch (InvalidArgumentException $e) {

        }
    }

    public function testGetModuleInstance_SingleInstance()
    {
        $mod = $this->object->getModuleInstance("PWE\\Core\\DummyModule");
        $this->assertTrue($mod instanceof PWEModule);
    }

    public function testGetModuleInstance_MultiInstance()
    {
        $node = array();
        $node['!a']['class'] = "PWE\\Core\\DummyModule";
        $node['!a']['src'] = "test.html";
        $mod = $this->object->getModuleInstance($node);
        $this->assertTrue($mod instanceof PWEModule);
    }

}

class AbstractPWECoreImpl extends AbstractPWECore {

    public function getCurrentModuleInstance()
    {
        return $this->currentModuleInstance;
    }

    public function createModulesManager(PWEModulesManager $externalManager = null)
    {
        parent::createModulesManager($externalManager);
    }

    public function getModulesManager()
    {
        try {
            return parent::getModulesManager();
        } catch (BadFunctionCallException $e) {
            return null;
        }
    }

}


class DummyModule extends PWEModule {}
?>
