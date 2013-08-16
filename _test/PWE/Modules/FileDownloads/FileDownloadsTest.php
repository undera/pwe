<?php
namespace PWE\Modules\FileDownloads;

require_once dirname(__FILE__) . '/../../../PWEUnitTests.php';

class FileDownloadsTest extends \PHPUnit_Framework_TestCase {

    public function test_zip_filter()
    {
        $this->assertTrue(FileDownloads::filter_out_cnt("test.zip", NULL, NULL));
        $this->assertFalse(FileDownloads::filter_out_cnt("test.zip.cnt", NULL, NULL));
    }
}
