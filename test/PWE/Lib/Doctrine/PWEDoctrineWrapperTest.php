<?php

namespace PWE\Lib\Doctrine;

require_once dirname(__FILE__) . '/../../../PWEUnitTests.php';

/**
 * Test class for PWEDoctrineWrapper.
 * Generated by PHPUnit on 2011-09-16 at 20:58:22.
 */
class PWEDoctrineWrapperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var PWEDoctrineWrapper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $pwe = new \PWE\Core\UnitTestPWECore();
        $db_config = array('driverClass' => '\PWE\Lib\Doctrine\PWEDoctrineEmulator', 'charset' => 'utf8');
        $db_settings['connection'][0]['!a'] = $db_config;
        /**
         * @var \PWE\Modules\TestPWEModulesManager
         */
        $modm = $pwe->getModulesManager();
        $modm->setModuleSettings("PWE\Lib\Doctrine\PWEDoctrineWrapper", $db_settings);

        $this->object = new PWEDoctrineWrapper($pwe);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @todo Implement testGetConnection().
     */
    public function testGetConnection() {
        $this->object->getConnection(new \PWE\Core\UnitTestPWECore());
    }

    /**
     * @todo Implement testSetup().
     */
    public function testSetup() {
        $registerData = array();
        $this->object->setup($this->object->getPWE(), $registerData);
    }

    public function testProcessUpgrade() {
        $this->object->processDBUpgrade('test');
    }

    public function testSoakFile() {
        $this->object->soakFile(__FILE__);
    }

}

?>