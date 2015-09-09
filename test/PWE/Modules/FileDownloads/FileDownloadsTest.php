<?php
namespace PWE\Modules\FileDownloads;

use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../../PWEUnitTests.php';

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
        PWELogger::info("Create dir " . $tmp);

        file_put_contents($tmp . '/first', time());
        file_put_contents($tmp . '/second', time());

        PWELogger::debug("File 1 " . file_get_contents($tmp . '/first'));
        PWELogger::debug("File 2 " . file_get_contents($tmp . '/second'));

        $node = &$pwe->getNode();
        $node['!a']['files_base'] = '.';

        $obj = new FileDownloads($pwe);
        $res = $obj->getDirectoryBlock('.');
        PWELogger::debug("DIR: " . $res);
        $this->assertGreaterThan(strpos($res, 'second'), strpos($res, 'first'));
    }
}
