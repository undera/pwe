<?php

namespace PWE\Core;

use ExceptionExpected;

require_once dirname(__FILE__) . '/../../PWEUnitTests.php';

class PWELoggerTest extends \PHPUnit_Framework_TestCase {

    public function testSetLevel() {
        PWELogger::setLevel(PWELogger::DEBUG);
        PWELogger::getLevelByName("debug");
        PWELogger::getLevelByName("info");
    }

    public function testSetStdErr() {
        PWELogger::setStdErr("php://stderr");
    }

    public function testSetStdOut() {
        PWELogger::setStdOut("php://stdout");
    }

    public function testDebug() {
        PWELogger::debug("Debugging");
        PWELogger::debug("Debugging", $this);
        PWELogger::debug("Debugging", debug_backtrace());
        PWELogger::debug("Debugging", debug_backtrace());
    }

    public function testInfo() {
        PWELogger::info("informing");
    }

    public function testWarning() {
        PWELogger::setStdErr("php://stdout");
        PWELogger::warning("Warn people");
    }

    public function testError() {
        PWELogger::setStdErr("php://stdout");
        PWELogger::error("Errors are bad");
    }

    public function testError_exc() {
        PWELogger::setStdErr("php://stdout");
        PWELogger::error("Errors are bad", new ExceptionExpected());
        PWELogger::debug(new ExceptionExpected());
    }

}

?>
