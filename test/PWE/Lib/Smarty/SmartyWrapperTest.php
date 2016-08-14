<?php

namespace PWE\Lib\Smarty;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../../PWEUnitTests.php';

class SmartyWrapperTest extends \PHPUnit_Framework_TestCase implements SmartyAssociative
{

    /**
     * @var SmartyWrapper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new SmartyWrapper(new UnitTestPWECore());
        $this->object->addTemplateDir('.');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @todo Implement testSetTemplateFile().
     */
    public function testSetTemplateFile()
    {
        $this->object->setTemplateFile('');
    }

    /**
     * @todo Implement testFetchAll().
     */
    public function testFetchAll()
    {
        $this->object->setTemplateFile(__FILE__);
        $this->object->fetchAll();
    }

    /**
     * @todo Implement testRegisterObject().
     */
    public function testRegisterObject()
    {
        $this->object->registerObject('test', $this);
    }

    public static function getSmartyAllowedMethods()
    {
        return array();
    }

    /**
     * @todo Implement testSetup().
     */
    public function testSetup()
    {
        $registerData = array();
        SmartyWrapper::setup(new UnitTestPWECore(), $registerData);
    }

}
