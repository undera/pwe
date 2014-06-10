<?php

namespace PWE\Utils;

use InvalidArgumentException;
use PWEUnitTests;
use RuntimeException;

require_once __DIR__ . '/../../PWEUnitTests.php';

class PWEXMLTest extends \PHPUnit_Framework_TestCase {

    public function testFileToArray_NoCache() {
        $xml = new PWEXML();

        $xml->FileToArray(__DIR__ . '/PWEXMLTest1.xml', $arr);
    }

    public function testFileToArray_CacheSmall() {
        $xml = new PWEXML(PWEUnitTests::utGetCleanTMP());
        // not cached
        $xml->FileToArray(__DIR__ . '/PWEXMLTest1.xml', $arr1);
        // cached
        $xml->FileToArray(__DIR__ . '/PWEXMLTest1.xml', $arr2);

        $this->assertEquals(md5(serialize($arr1)), md5(serialize($arr2)));
    }

    public function testFileToArray_CacheHuge() {
        $tmp = PWEUnitTests::utGetCleanTMP();
        $xml = new PWEXML($tmp);
        // not cached
        $xml->FileToArray(__DIR__ . '/PWEXMLTestHuge.xml', $arr1);
        //PWEUnitTests::dumpArrayToFile($arr1, $tmp.'/arr1.txt');
        // cached
        $xml->FileToArray(__DIR__ . '/PWEXMLTestHuge.xml', $arr2);
        //PWEUnitTests::dumpArrayToFile($arr2, $tmp.'/arr2.txt');

        $this->assertEquals(md5(serialize($arr1)), md5(serialize($arr2)));
    }

    public function testFileToArray_BrokenMultisave() {
        $tmp = PWEUnitTests::utGetCleanTMP();
        $xml = new PWEXML($tmp);
        $xml->use_cache = false;
        copy(__DIR__ . '/PWEXMLTestHuge.xml', $tmp . '/src.xml');

        $arr1 = array();
        for ($i = 0; $i < 10; $i++) {
            $xml->FileToArray(__DIR__ . '/PWEXMLTest1.xml', $arr1);
            $xml->ArrayToFile($arr1, $tmp . '/src.xml');
        }
    }

    public function testFileToArray_NoFile() {
        $xml = new PWEXML(PWEUnitTests::utGetCleanTMP());

        try {
            $xml->FileToArray(__DIR__ . '/PWEXMLTestNoFile.xml', $arr);
            throw new Exception("Exception expected");
        } catch (RuntimeException $e) {
            
        }
    }

    public function testFileToArray_Broken() {
        $xml = new PWEXML(PWEUnitTests::utGetCleanTMP());

        try {
            $xml->FileToArray(__DIR__ . '/PWEXMLTestBroken.xml', $arr);
            throw new Exception("Exception expected");
        } catch (RuntimeException $e) {
            
        }
    }

    public function testArrayToFile_notexists() {
        $xml = new PWEXML();
        try {
            $a = array();
            $xml->ArrayToFile($a, '');
            throw new Exception("Exception expected");
        } catch (InvalidArgumentException $e) {
            
        }
    }

    public function testArrayToFile() {
        $xml = new PWEXML();
        $arr = array();
        $arr['root'][0] = array('!v' => "Test\nTest", '!a' => array('a' => 'v'));
        $arr['root'][0]['!c'] = $arr;
        $xml->ArrayToFile($arr, PWEUnitTests::utGetCleanTMP() . '/test1.xml');
        // to drop previous file
        $xml->ArrayToFile($arr, PWEUnitTests::utGetCleanTMP() . '/test1.xml');
    }

    public function testBrokenWrite() {
        $tmp = PWEUnitTests::utGetCleanTMP();
        $registerData = array();
        $registerData['!a']['dir'] = '/tmp';
        $registerData['!c']['ArchiveToken'] = array('!v' => 'changeme');
        $xml = new PWEXML();
        $data = array('test' => array($registerData));
        try {
            $xml->ArrayToFile($data, $tmp . '/test.xml');
            $this->fail();
        } catch (RuntimeException $e) {
            $this->assertFileNotExists($tmp . '/test.xml');
        }
    }

}

?>