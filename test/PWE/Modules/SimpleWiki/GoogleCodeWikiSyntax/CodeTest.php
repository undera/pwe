<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

class CodeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \WikiRenderer\Renderer
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new \WikiRenderer\Renderer(new Config());
    }

    public function testGetRenderedLine_anchored()
    {
        $res = $this->object->render("[[[python\nprint(1+varname)\n]]]");
        $this->assertEquals('', $res);
    }

}
