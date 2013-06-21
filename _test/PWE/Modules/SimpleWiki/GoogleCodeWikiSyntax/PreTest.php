<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

class PreTest extends \PHPUnit_Framework_TestCase {

    public function testGetRenderedLine() {
        $obj = new \WikiRenderer\Renderer(new Config());
        $str = "{{{ \n some text \n }}}";
        $res = $obj->render($str);
        $this->assertEquals("<pre>\n some text \n</pre>", $res);
    }

    public function testGetRenderedLine_prepended() {
        $obj = new \WikiRenderer\Renderer(new Config());
        $str = "Some prep text {{{ \n some text \n }}}";
        $res = $obj->render($str);
        $this->assertEquals("Some prep text <pre>\n some text \n</pre>", $res);
    }

    public function testGetRenderedLine_noPre() {
        $obj = new \WikiRenderer\Renderer(new Config());
        $str = "{{{ some text }}}";
        $res = $obj->render($str);
        $this->assertEquals("<p><tt> some text </tt></p>", $res);
    }

}
