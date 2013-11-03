<?php
namespace PWE\Modules\FileDownloads;

use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;

require_once dirname(__FILE__) . '/../../../PWEUnitTests.php';

class FileDownloadsTest extends \PHPUnit_Framework_TestCase
{

    public function test_zip_filter()
    {
        $this->assertTrue(FileDownloads::filter_out_cnt("test.zip"));
        $this->assertFalse(FileDownloads::filter_out_cnt("test.zip.cnt"));
    }

    public function test_directory_sorted()
    {
        $pwe = new UnitTestPWECore();
        $pwe->setURL('/');
        $tmp = $pwe->getTempDirectory();
        $pwe->setRootDirectory($tmp);
        $pwe->setTempDirectory($tmp);
        PWELogger::info("Create dir " . $pwe->getTempDirectory());
        //mkdir($pwe->getTempDirectory(), 0x777, true);

        $dir = $pwe->getTempDirectory();
        file_put_contents($dir . '/test1', time());
        file_put_contents($dir . '/test2', time());

        PWELogger::debug(file_get_contents($dir . '/test1'));
        PWELogger::debug(file_get_contents($dir . '/test2'));

        $node = $pwe->getNode();
        $node['!a']['files_base'] = '.';
        $pwe->setNode($node);

        $obj = new FileDownloads($pwe);
        $res = $obj->getDirectoryBlock('.');
        PWELogger::debug("DIR: " . $res);
        $this->assertGreaterThan(strpos($res, 'test2'), strpos($res, 'test1'));
    }
}
