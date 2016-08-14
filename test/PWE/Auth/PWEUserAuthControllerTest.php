<?php

namespace PWE\Auth;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../PWEUnitTests.php';


class PWEUserAuthControllerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var PWEUserAuthControllerImpl
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new PWEUserAuthControllerImpl();
    }

    public function testGetSmartyAllowedMethods()
    {
        $this->object->getSmartyAllowedMethods();
    }

    public function testGetAuthControllerInstance()
    {
        $this->object->getPWE()->setURL('/');
        PWEUserAuthController::getAuthControllerInstance($this->object->getPWE());
    }

    public function testGetLevelsUpToAuthNode()
    {
        $this->object->getPWE()->setURL('/');
        $this->object->getLevelsUpToAuthNode();
    }

}

class PWEUserAuthControllerImpl extends PWEUserAuthController
{

    public function __construct()
    {
        parent::__construct(new UnitTestPWECore());
    }

    public function getUserID()
    {

    }

    public function getUserName()
    {

    }

    public function handleAuth()
    {

    }

    public function handleLogout()
    {

    }

}
