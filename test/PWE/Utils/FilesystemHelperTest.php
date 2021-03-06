<?php

namespace PWE\Utils;

use PWE\Core\UnitTestPWECore;

require_once __DIR__ . '/../../PWEUnitTests.php';


class FilesystemHelperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FilesystemHelper
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //$this->object = new FilesystemHelper;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @todo Implement testFsys_readdir().
     */
    public function testFsys_readdir()
    {
        $PWE = new UnitTestPWECore();
        FilesystemHelper::fsys_readdir(__DIR__);
        FilesystemHelper::fsys_copydir(__DIR__, $PWE->getTempDirectory());
        FilesystemHelper::fsys_kbytes(100000);
        FilesystemHelper::fsys_filesize(__FILE__);
        FilesystemHelper::fsys_filesize(__DIR__);
    }

}

