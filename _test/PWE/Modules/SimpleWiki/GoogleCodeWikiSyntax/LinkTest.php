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
        $this->assertEquals('<p><a href="../WikiSyntax#Wiki-style_markup">WikiSyntax</a></p>', $res);
    }

    public function testGetRenderedLine() {
        $res = $this->object->render("[Link Text Several]");
        $this->assertEquals('<p><a href="../Link">Text Several</a></p>', $res);
    }

    public function testGetRenderedLine_complex() {
        $res = $this->object->render("[http://loadosophia.org/?utmsource=jpgc&utmmedium=link&utm_campaign=wiki Loadosophia.org]");
        $this->assertEquals('<p><a href="http://loadosophia.org/?utmsource=jpgc&utmmedium=link&utm_campaign=wiki">Loadosophia.org</a></p>', $res);
    }

    public function testGetRenderedLine_link() {
        $res = $this->object->render("[Link]");
        $this->assertEquals('<p><a href="../Link">Link</a></p>', $res);
    }

    public function testGetRenderedLine_img() {
        $res = $this->object->render("[img.png comment text]");
        $this->assertEquals('<p><img src="img.png" alt="comment text"/></p>', $res);
    }

    public function testGetRenderedLine_img1() {
        $res = $this->object->render("[http://jmeter-plugins.googlecode.com/svn-history/wiki/img/loadosophia_uploader.png]");
        $this->assertEquals('<p><img src="http://jmeter-plugins.googlecode.com/svn-history/wiki/img/loadosophia_uploader.png" alt=""/></p>', $res);
    }

}
