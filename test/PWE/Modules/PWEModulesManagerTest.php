<?php

namespace PWE\Modules;

use PWE\Core\UnitTestPWECore;

require_once dirname(__FILE__) . '/../../PWEUnitTests.php';

class PWEModulesManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PWEModulesManager
     */
    protected $object;
    private $pwe;

    protected function setUp()
    {
        $this->pwe = new UnitTestPWECore();
        copy(dirname(__FILE__) . '/registry.xml', $this->pwe->getTempDirectory() . '/eg_globals.xml');
        $this->object = new PWEModulesManager($this->pwe);
    }

    public function testGetModuleSettings()
    {
        $this->object->getModuleSettings(__CLASS__);
    }

    public function testGetSingleInstanceModule()
    {
        $this->object->getSingleInstanceModule(__CLASS__);
    }

    public function testGetMultiInstanceModule()
    {
        $a['!a']['class'] = __CLASS__;
        $this->object->getMultiInstanceModule($a);
    }

    public function testRun()
    {
        $this->object->run();
    }

    public function testRegister()
    {
        $a['!a']['class'] = __CLASS__;
        $this->object->registerModule(__CLASS__);
    }

}

?>