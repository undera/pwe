<?php

namespace PWE\Core;

require_once dirname(__FILE__) . '/../../PWEUnitTests.php';

use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use \Exception;

class PWEURLTest extends \PHPUnit_Framework_TestCase {

    public function testGetProposedRedirect_None() {
        $obj = new PWEURL('/');
    }

    public function testGetProposedRedirect_Dot() {
        try {
            $obj = new PWEURL('/./');
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/", $e->getMessage());
        }
    }

    public function testGetProposedRedirect_DotDot() {
        try {
            $obj = new PWEURL('/somepath1/../somepath2');
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/somepath1/somepath2", $e->getMessage());
        }
    }

    public function testGetProposedRedirect_no_slash_file() {
        $obj = new PWEURL('/img/image.gif');
    }

    public function testGetProposedRedirect_no_slash_not_file() {
        try {
            $obj = new PWEURL('/img/image-gif');
            throw new Exception("302 expected");
        } catch (HTTP3xxException $e) {
            $this->assertEquals('/img/image-gif/', $e->getMessage());
        }
    }

    public function testGetProposedRedirect_incorrectURI() {
        try {
            $obj = new PWEURL('/service/?refresh=:/');
            // FIXME: hmmmmm... what to do?
            //throw new Exception("400 expected"); 
        } catch (HTTP4xxException $e) {
            $this->assertEquals(HTTP4xxException::BAD_REQUEST, $e->getCode());
        }
    }

    public function testGetProposedRedirect_incorrectURI2() {
        try {
            $obj = new PWEURL('service');
            throw new Exception("400 expected");
        } catch (HTTP4xxException $e) {
            $this->assertEquals(HTTP4xxException::BAD_REQUEST, $e->getCode());
        }
    }

    public function testGetFullAsArray() {
        $obj = new PWEURL('/1/2/3/');
        $this->assertEquals(array('', '1', '2', '3'), $obj->getFullAsArray());
    }

    public function testGetFullAsArray_trickyEnv() {
        $_SERVER["DOCUMENT_ROOT"] = '/var/www';
        $_SERVER["SCRIPT_FILENAME"] = '/usr/share/php/pwe/index.php';
        $obj = new PWEURL('/service/');
        $this->assertEquals(array('', 'service'), $obj->getFullAsArray());
    }

    public function testGetFullAsArray_subdirWork() {
        $_SERVER["DOCUMENT_ROOT"] = '/var/www';
        $_SERVER["SCRIPT_FILENAME"] = '/var/www/subdir1/subdir2/index.php';
        $obj = new PWEURL('/subdir1/subdir2/service/');
        $this->assertEquals(array('', 'service'), $obj->getFullAsArray());
    }

    public function testGetParamsAsArray() {
        $_SERVER["DOCUMENT_ROOT"] = dirname($_SERVER["SCRIPT_FILENAME"]);
        $obj = new PWEURL('/1/2/3/');
        $this->assertEquals(array('', '1', '2', '3'), $obj->getFullAsArray());
    }

    public function testsetMatchedDepth() {
        $_SERVER["DOCUMENT_ROOT"] = dirname($_SERVER["SCRIPT_FILENAME"]);
        $obj = new PWEURL('/1/2/3/');

        $obj->setMatchedDepth(3);

        $this->assertEquals(array('', '1', '2'), $obj->getMatchedAsArray());
        $this->assertEquals(array('3'), $obj->getParamsAsArray());
    }

}

?>