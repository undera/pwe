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
        $tmp=$pwe->getTempDirectory();
        $pwe->setRootDirectory($tmp);
        $pwe->setTempDirectory($tmp);
        PWELogger::info("Create dir " . $pwe->getTempDirectory());
        //mkdir($pwe->getTempDirectory(), 0x777, true);

        file_put_contents($pwe->getTempDirectory() . '/test1', time());
        sleep(1);
        file_put_contents($pwe->getTempDirectory() . '/test2', time());

        $node = $pwe->getNode();
        $node['!a']['files_base'] = '.';
        $pwe->setNode($node);

        $obj = new FileDownloads($pwe);
        $res = $obj->getDirectoryBlock('.');
        PWELogger::debug("DIR: " . $res);
        $this->assertGreaterThan(strpos($res, 'test2'), strpos($res, 'test1'));
    }
}
