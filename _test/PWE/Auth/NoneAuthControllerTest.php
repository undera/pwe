<?php

namespace PWE\Auth;

require_once dirname(__FILE__) . '/../../PWEUnitTests.php';

/**
 * Test class for NoneAuthController.
 * Generated by PHPUnit on 2011-09-12 at 15:15:58.
 */
class NoneAuthControllerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var NoneAuthController
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->pwe = new \PWE\Core\UnitTestPWECore;
        $this->object = new NoneAuthController($this->pwe);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @todo Implement testGetUserID().
     */
    public function testGetUserID() {
        $this->object->getUserID();
    }

    /**
     * @todo Implement testHandleAuth().
     */
    public function testHandleAuth() {
        $this->object->handleAuth();
    }

    /**
     * @todo Implement testGetUserName().
     */
    public function testGetUserName() {
        $this->object->getUserName();
    }

    /**
     * @todo Implement testHandleLogout().
     */
    public function testHandleLogout() {
        $this->object->handleLogout();
    }

}

?>
