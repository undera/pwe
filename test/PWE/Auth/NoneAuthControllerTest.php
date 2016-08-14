<?php

namespace PWE\Auth;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../PWEUnitTests.php';

class NoneAuthControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var NoneAuthController
     */
    protected $object;
    protected $pwe;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->pwe = new UnitTestPWECore;
        $this->object = new NoneAuthController($this->pwe);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testGetUserID()
    {
        $this->object->getUserID();
    }

    public function testHandleAuth()
    {
        $this->object->handleAuth();
    }

    public function testGetUserName()
    {
        $this->object->getUserName();
    }

    public function testHandleLogout()
    {
        $this->object->handleLogout();
    }

}
