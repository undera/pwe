<?php

namespace PWE\Core;

require_once __DIR__ . '/../../PWEUnitTests.php';

use Exception;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;

class PWEURLTest extends \PHPUnit_Framework_TestCase
{
    private $struct = array('url' => array(array()));

    public function testGetProposedRedirect_None()
    {
        $obj = new PWEURL('/', $this->struct);
        $node1 = & $obj->getNode();
        $node1['!a']['test'] = 1;
        $node2 = & $obj->getNode();
        $this->assertEquals(1, $node2['!a']['test']);
    }

    public function testGetProposedRedirect_Dot()
    {
        try {
            $obj = new PWEURL('/./', $this->struct);
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/", $e->getMessage());
        }
    }

    public function testGetProposedRedirect_DotDot()
    {
        try {
            $obj = new PWEURL('/somepath1/../somepath2', $this->struct);
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/somepath1/somepath2", $e->getMessage());
        }
    }

    public function testGetProposedRedirect_no_slash_file()
    {
        try {
            $obj = new PWEURL('/img/image.gif', $this->struct);
            $this->fail();
        } catch (HTTP4xxException $e) {
        }
    }

    public function testGetProposedRedirect_no_slash_not_file()
    {
        try {
            $obj = new PWEURL('/img/image-gif', $this->struct);
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('/img/image-gif/', $e->getMessage());
        }
    }

    public function testGetProposedRedirect_no_slash_get_params()
    {
        $_GET['test'] = 1;
        $_GET['test2'] = 2;
        try {
            $obj = new PWEURL('/img', $this->struct);
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('/img/?test=1&test2=2', $e->getMessage());
        }
    }

    public function testGetProposedRedirect_incorrectURI()
    {
        try {
            $obj = new PWEURL('/service/?refresh=:/', $this->struct);
            $this->fail();
        } catch (HTTP4xxException $e) {
            $this->assertEquals(HTTP4xxException::NOT_FOUND, $e->getCode());
        }
    }

    public function testGetProposedRedirect_incorrectURI2()
    {
        try {
            $obj = new PWEURL('service', $this->struct);
            throw new Exception("400 expected");
        } catch (HTTP4xxException $e) {
            $this->assertEquals(HTTP4xxException::BAD_REQUEST, $e->getCode());
        }
    }

    public function testGetFullAsArray()
    {
        $obj = new PWEURL('/1/2/3/', $this->struct);
        $this->assertEquals(array(''), $obj->getFullAsArray());
    }

    public function testGetFullAsArray_trickyEnv()
    {
        $_SERVER["DOCUMENT_ROOT"] = '/var/www';
        $_SERVER["SCRIPT_FILENAME"] = '/usr/share/php/pwe/index.php';
        $obj = new PWEURL('/service/', $this->struct);
        $this->assertEquals(array('', 'service'), $obj->getFullAsArray());
    }

    public function testGetFullAsArray_subdirWork()
    {
        $_SERVER["DOCUMENT_ROOT"] = '/var/www';
        $_SERVER["SCRIPT_FILENAME"] = '/var/www/subdir1/subdir2/index.php';
        $obj = new PWEURL('/subdir1/subdir2/service/', $this->struct);
        $this->assertEquals(array('', 'service'), $obj->getFullAsArray());
    }

    public function testGetParamsAsArray()
    {
        $_SERVER["DOCUMENT_ROOT"] = dirname($_SERVER["SCRIPT_FILENAME"]);
        $obj = new PWEURL('/1/2/3/', $this->struct);
        $this->assertEquals(array('', '1', '2', '3'), $obj->getFullAsArray());
    }

}

?>