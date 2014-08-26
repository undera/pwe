<?php

namespace PWE\Core;

use BadFunctionCallException;
use Exception;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Modules\MenuGenerator;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Modules\PWEModulesManager;
use PWEUnitTests;

require_once __DIR__ . '/../../PWEUnitTests.php';

class WebPWECoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var WebPWECore
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new PWECoreEmul();
        $this->object->setDataDirectory(__DIR__);
        $this->object->setXMLDirectory(__DIR__ . '/coreXML');
        $this->object->setTempDirectory(PWEUnitTests::utGetCleanTMP());
        $this->object->createModulesManager();
    }

    public function testSetURL_RootJTFC()
    {
        try {
            $this->object->process('/');
            throw new \Exception("Exception expected");
        } catch (HTTP3xxException $e) {

        }
    }

    public function testSetURL_Page1Success()
    {
        $this->object->process('/test/subnode/');
    }

    public function testSetURL_Redirect()
    {
        try {
            $this->object->process('/../123/');
            throw new Exception("Exception expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/123/", $e->getMessage());
        }
    }

    public function testSetURL_AcceptParams()
    {
        $this->object->process('/accept/123/');
    }

    public function testEmptyTPL()
    {
        $this->object->process('/notpl/');
    }

    public function testSetURL_AcceptParamsFile()
    {
        $this->object->process('/accept/text.xml');
    }

    public function testSetURL_AcceptParamsFile_at_root()
    {
        try {
            $this->object->process('/robots.txt');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::NOT_FOUND) {
                throw $e;
            }
        }
    }

    public function testSetURL_AcceptParamsExceeded()
    {
        try {
            $this->object->process('/accept/123/123/123/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::BAD_REQUEST) {
                throw $e;
            }
        }
    }

    public function testSetURL_Page1FailWithParams()
    {
        try {
            $this->object->process('/test/123/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::NOT_FOUND) {
                throw $e;
            }
        }
    }

    public function testSetURL_MidParams_NotAll()
    {
        $this->object->process('/midaccept/123/edit/');
    }

    public function testSetURL_MidParams_All()
    {
        $this->object->process('/midaccept/123/456/edit/');
    }

    public function testSetURL_MidParamsExceeded()
    {
        try {
            $this->object->process('/midaccept/123/123/123/edit/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::BAD_REQUEST) {
                throw $e;
            }
        }
    }


    public function testSetURL_Notexistent()
    {
        try {
            $this->object->process('/321/123/');
            $this->fail();
        } catch (HTTP4xxException $e) {
            if ($e->getCode() != HTTP4xxException::NOT_FOUND) {
                throw $e;
            }
        }
    }

    public function testGetURL()
    {
        $this->object->process('/accept/123/');
        $res = $this->object->getURL();
    }

    public function testProcess_JTFC302()
    {
        try {
            $this->object->process('/');
            $this->fail();
        } catch (HTTP3xxException $e) {

        }
    }

    public function testProcess_ok()
    {
        $res = $this->object->process('/test/subnode/');
        $this->assertGreaterThan(5000, strlen($res));
    }

    public function testProcess_404()
    {
        try {
            $this->object->process('/test/subnode/404/');
            $this->fail();
        } catch (HTTP4xxException $e) {

        }
    }

    public function test_getHeader()
    {
        $_SERVER['HTTP_TEST_TEST'] = "passed";
        $res = $this->object->getHeader('test-test');
        $this->assertEquals("passed", $res);
    }

    public function testSetDisplayTemplate()
    {
        $this->object->setDisplayTemplate('');
    }

    public function testGetDisplayTemplate()
    {
        $this->object->getDisplayTemplate();
    }

    public function testGetEmptyTemplate()
    {
        $this->object->getEmptyTemplate();
    }


}

class PWECoreEmul extends WebPWECore
{

    public $HTTPStatus;

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

    public function sendHTTPStatusCode($code)
    {
        parent::sendHTTPStatusCode($code);
        $this->HTTPStatus = $code;
    }


}

class TestModule extends PWEModule implements Outputable, MenuGenerator
{

    public function process()
    {

    }

    public function getMenuLevel($level)
    {

    }

}

?>