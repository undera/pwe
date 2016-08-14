<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

class PragmaTest extends \PHPUnit_Framework_TestCase
{

    public function testGetRenderedLine()
    {
        $obj = new \WikiRenderer\Renderer(new Config());
        $str = "#labels Deprecated,Restrict-AddWikiComment-Commit\nsometext\n#labels valid";
        $res = $obj->render($str);
//        $this->assertEquals("<!--#labels Deprecated,Restrict-AddWikiComment-Commit--> \n<p>sometext</p>\n#labels valid", $res);
    }
}
