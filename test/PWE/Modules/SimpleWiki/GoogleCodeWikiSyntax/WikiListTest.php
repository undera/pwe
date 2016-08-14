<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

class WikiListTest extends \PHPUnit_Framework_TestCase
{

    public function test_ordered()
    {
        $obj = new \WikiRenderer\Renderer(new Config());
        $str = "  # ordered";
        $res = $obj->render($str);
        $this->assertEquals("<ol>\n<li>ordered</li></ol>\n", $res);
    }

    public function test_unordered()
    {
        $obj = new \WikiRenderer\Renderer(new Config());
        $str = "  * unordered";
        $res = $obj->render($str);
        $this->assertEquals("<ul>\n<li>unordered</li></ul>\n", $res);
    }

}
