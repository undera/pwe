<?php

namespace PWE\Lib\Doctrine;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../../PWEUnitTests.php';

class PWEDoctrineWrapperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PWEDoctrineWrapper
     */
    protected $object;
    protected $PWE;

    protected function setUp()
    {
        $this->PWE = new UnitTestPWECore();
        $db_config = array('driverClass' => get_class(new PWEDoctrineEmulator()), 'charset' => 'utf8');
        $db_settings['!c']['connection'][0]['!a'] = $db_config;
        $db_settings['!c']['connection'][1]['!a'] = array_merge($db_config, array('alias' => 'aliased'));
        $modm = $this->PWE->getModulesManager();
        $modm->setModuleSettings(PWEDoctrineWrapper::getClass(), $db_settings);

        $this->object = new PWEDoctrineWrapperEmul($this->PWE);
    }

    public function testGetConnection_single()
    {
        $this->object->getConnection($this->PWE, true);
        $this->object->getConnection($this->PWE, false);
    }

    public function testGetConnection_alias_not_found()
    {
        try {
            $this->object->getConnection($this->PWE, true, 'notexist');
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            //pass
        }
    }

    public function testGetConnection_alias()
    {
        $this->object->getConnection($this->PWE, true, 'aliased');
    }

    public function testSetup()
    {
        $registerData = array();
        $this->object->setup($this->PWE, $registerData);
    }

    public function testProcessUpgrade()
    {
        $DB = PWEDoctrineWrapper::getConnection($this->PWE);
        $this->object->processDBUpgrade($DB, 'test');
    }

    public function testSoakFile()
    {
        $DB = PWEDoctrineWrapper::getConnection($this->PWE);
        $this->object->soakFile($DB, __FILE__);
    }

    public function testRun()
    {
        global $argv;
        $argv[] = '-a';
        $argv[] = '';
        $argv[] = '-m';
        $argv[] = 'TEST';
        $argv[] = '-p';
        $argv[] = __DIR__;

        $this->object->run();
    }
}


class PWEDoctrineWrapperEmul extends PWEDoctrineWrapper
{
    protected function getOpts()
    {
        return array('m' => 'test', 'p' => __DIR__);
    }

}