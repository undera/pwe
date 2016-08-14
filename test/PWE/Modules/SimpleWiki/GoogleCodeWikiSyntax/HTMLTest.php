<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

class HTMLTest extends \PHPUnit_Framework_TestCase
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
        $res = $this->object->render('<i><font color=gray size="1">Nov 4, 2010</font></i>');
        $this->assertEquals('<p><i><font color=gray size="1">Nov 4, 2010</font></i></p>', $res);
    }

}
