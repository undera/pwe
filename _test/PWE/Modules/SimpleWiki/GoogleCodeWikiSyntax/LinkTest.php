<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

class LinkTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \WikiRenderer\Renderer
     */
    protected $object;

    protected function setUp() {
        $this->object = new \WikiRenderer\Renderer(new Config());
    }

    public function testGetRenderedLine_anchored() {
        $res = $this->object->render("[WikiSyntax#Wiki-style_markup]");
        $this->assertEquals('<p><a href="WikiSyntax#Wiki-style_markup">WikiSyntax</a></p>', $res);
    }

    public function testGetRenderedLine() {
        $res = $this->object->render("[Link Text Several]");
        $this->assertEquals('<p><a href="Link">Text Several</a></p>', $res);
    }

    public function testGetRenderedLine_link() {
        $res = $this->object->render("[Link]");
        $this->assertEquals('<p><a href="Link">Link</a></p>', $res);
    }

    public function testGetRenderedLine_img() {
        $res = $this->object->render("[img.png comment text]");
        $this->assertEquals('<p><img src="img.png" alt="comment text"/></p>', $res);
    }

}
