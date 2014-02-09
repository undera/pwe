<?php
namespace PWE\Utils;


use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;

class CSSJSPreprocessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $pwe = new UnitTestPWECore();
        copy(__DIR__ . '/preprocess.tpl', $pwe->getTempDirectory() . '/test.tpl');

        $obj = new CSSJSPreprocessor($pwe);
        $obj->preprocess($pwe->getTempDirectory(), $pwe->getTempDirectory());

        $test_tpl = file_get_contents($pwe->getTempDirectory() . '/test.tpl');
        $pwe_js = file_get_contents($pwe->getTempDirectory() . '/pwe.js');
        $pwe_css = file_get_contents($pwe->getTempDirectory() . '/pwe.css');

        PWELogger::debug("TPL: $test_tpl");
        PWELogger::debug("JS: $pwe_js");
        PWELogger::debug("CSS: $pwe_css");

        $this->assertContains("do_process_css", $pwe_css);
        $this->assertNotContains("not_process_css", $pwe_css);
        $this->assertContains("not_process_css", $test_tpl);
        $this->assertNotContains("do_process_css", $test_tpl);

        $this->assertContains("do_process_js", $pwe_js);
        $this->assertNotContains("not_process_js", $pwe_js);
        $this->assertContains("not_process_js", $test_tpl);
        $this->assertNotContains("do_process_js", $test_tpl);
    }

}
 