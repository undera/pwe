<?php

namespace PWE\Utils;

use PWE\Core\PWEURL;

require_once __DIR__ . '/../../PWEUnitTests.php';

class PWEUtilsTest extends \PHPUnit_Framework_TestCase {

    public function testProtectAgainsRelativePaths_1() {
        $path = "somedir/./subdir/../test.php";
        $expected = "somedir/test.php";
        $res = PWEURL::protectAgainsRelativePaths($path);
        $this->assertEquals($expected, $res);
    }

    public function testProtectAgainsRelativePaths_2() {
        $path = "somedir/./subdir/../../test.php";
        $expected = "test.php";
        $res = PWEURL::protectAgainsRelativePaths($path);
        $this->assertEquals($expected, $res);
    }

    public function testProtectAgainsRelativePaths_3() {
        $path = "/somedir/./subdir/../test.php";
        $expected = "/somedir/test.php";
        $res = PWEURL::protectAgainsRelativePaths($path);
        $this->assertEquals($expected, $res);
    }

}

?>
