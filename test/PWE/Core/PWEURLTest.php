<?php

namespace PWE\Core;

use Exception;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Utils\PWEXML;

class PWEURLTest extends \PHPUnit_Framework_TestCase
{
    private $struct = array('url' => array(array()));

    protected function setUp()
    {
        $xml = new PWEXML();
        $xml->FileToArray(__DIR__ . '/coreXML/out.xml', $this->struct);
    }

    public function testGetProposedRedirect_None()
    {
        $obj = new PWEURL('/', $this->struct);
        $node1 = &$obj->getNode();
        $node1['!a']['test'] = 1;
        $node2 = &$obj->getNode();
        $this->assertEquals(1, $node2['!a']['test']);
    }

    public function testGetProposedRedirect_Dot()
    {
        try {
            new PWEURL('/./', $this->struct);
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/", $e->getMessage());
        }
    }

    public function testGetProposedRedirect_DotDot()
    {
        try {
            new PWEURL('/somepath1/../somepath2', $this->struct);
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/somepath1/somepath2", $e->getMessage());
        }
    }

    public function testGetProposedRedirect_no_slash_file()
    {
        try {
            $obj = new PWEURL('/test/image.gif', $this->struct);

            if ($obj->getFailure()) {
                throw $obj->getFailure();
            }

            $this->fail();
        } catch (HTTP4xxException $e) {
        }
    }

    public function testGetProposedRedirect_no_slash_not_file()
    {
        try {
            new PWEURL('/test', $this->struct);
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('/test/', $e->getMessage());
        }
    }

    public function testGetProposedRedirect_slash_not_forced()
    {
        new PWEURL('/slash-not-forced', $this->struct);
    }

    public function testGetProposedRedirect_many_slashes()
    {
        try {
            new PWEURL('/test//', $this->struct);
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('/test/', $e->getMessage());
        }
    }

    public function testGetProposedRedirect_no_slash_get_params()
    {
        $_GET['test'] = 1;
        $_GET['test2'] = 2;
        try {
            new PWEURL('/img', $this->struct);
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('/img/?test=1&test2=2', $e->getMessage());
        }
    }

    public function testGetProposedRedirect_incorrectURI()
    {
        try {
            $obj = new PWEURL('/service/?refresh=:/', $this->struct);
            if ($obj->getFailure()) {
                throw $obj->getFailure();
            }
            $this->fail();
        } catch (HTTP4xxException $e) {
            $this->assertEquals(HTTP4xxException::NOT_FOUND, $e->getCode());
        }
    }

    public function testGetProposedRedirect_incorrectURI2()
    {
        try {
            new PWEURL('service', $this->struct);
            throw new Exception("400 expected");
        } catch (HTTP4xxException $e) {
            $this->assertEquals(HTTP4xxException::BAD_REQUEST, $e->getCode());
        }
    }

    public function testGetFullAsArray()
    {
        $n3 = array('!a' => array('link' => '3'));
        $n2 = array('!a' => array('link' => '2'));
        $n2['!c']['url'] = array($n3);
        $n1 = array('!a' => array('link' => '1'));
        $n1['!c']['url'] = array($n2);
        $n0 = array('!a' => array());
        $n0['!c']['url'] = array($n1);
        $struct = array('url' => array($n0));
        $obj = new PWEURL('/1/2/3/', $struct);
        $this->assertEquals(array('', '1', '2', '3'), $obj->getFullAsArray());
    }

    public function testGetFullAsArray_trickyEnv()
    {
        $n1 = array('!a' => array('link' => 'service'));
        $n0 = array('!a' => array());
        $n0['!c']['url'] = array($n1);
        $struct = array('url' => array($n0));

        $_SERVER["DOCUMENT_ROOT"] = '/var/www';
        $_SERVER["SCRIPT_FILENAME"] = '/usr/share/php/pwe/index.php';
        $obj = new PWEURL('/service/', $struct);
        $this->assertEquals(array('', 'service'), $obj->getFullAsArray());
    }

    public function testGetFullAsArray_subdirWork()
    {
        $n1 = array('!a' => array('link' => 'service'));
        $n0 = array('!a' => array());
        $n0['!c']['url'] = array($n1);
        $struct = array('url' => array($n0));

        $_SERVER["DOCUMENT_ROOT"] = '/var/www';
        $_SERVER["SCRIPT_FILENAME"] = '/var/www/subdir1/subdir2/index.php';
        $obj = new PWEURL('/subdir1/subdir2/service/', $struct);
        $this->assertEquals(array('', 'service'), $obj->getFullAsArray());
    }

    public function testGetParamsAsArray()
    {
        $n3 = array('!a' => array('link' => '3'));
        $n2 = array('!a' => array('link' => '2'));
        $n2['!c']['url'] = array($n3);
        $n1 = array('!a' => array('link' => '1'));
        $n1['!c']['url'] = array($n2);
        $n0 = array('!a' => array());
        $n0['!c']['url'] = array($n1);
        $struct = array('url' => array($n0));

        $_SERVER["DOCUMENT_ROOT"] = dirname($_SERVER["SCRIPT_FILENAME"]);
        $obj = new PWEURL('/1/2/3/', $struct);
        $this->assertEquals(array('', '1', '2', '3'), $obj->getFullAsArray());
    }

}
