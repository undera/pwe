<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Renderer;

class LinkTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Renderer
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Renderer(new Config());
    }

    public function testGetRenderedLine_anchored()
    {
        $res = $this->object->render("[WikiSyntax#Wiki-style_markup]");
        $this->assertEquals('<p><a href="../WikiSyntax#Wiki-style_markup">WikiSyntax#Wiki-style_markup</a></p>', $res);
    }

    public function testGetRenderedLine()
    {
        $res = $this->object->render("[Link Text Several]");
        $this->assertEquals('<p><a href="../Link">Text Several</a></p>', $res);
    }

    public function testGetRenderedLine_complex()
    {
        $res = $this->object->render("[http://loadosophia.org/?utmsource=jpgc&utmmedium=link&utm_campaign=wiki Loadosophia.org]");
        $this->assertEquals('<p><a href="http://loadosophia.org/?utmsource=jpgc&utmmedium=link&utm_campaign=wiki" target="_blank" rel="nofollow">Loadosophia.org</a></p>', $res);
    }

    public function testGetRenderedLine_link()
    {
        $res = $this->object->render("[Link]");
        $this->assertEquals('<p><a href="../Link">Link</a></p>', $res);
    }

    public function testGetRenderedLine_img()
    {
        $res = $this->object->render("[img.png comment text]");
        $this->assertEquals('<p><img src="../img.png" alt="comment text"/></p>', $res);
    }

    public function testGetRenderedLine_img1()
    {
        $res = $this->object->render("[http://jmeter-plugins.googlecode.com/svn-history/wiki/img/loadosophia_uploader.png]");
        $this->assertEquals('<p><img src="http://jmeter-plugins.googlecode.com/svn-history/wiki/img/loadosophia_uploader.png" alt=""/></p>', $res);
    }

    public function testGetRenderedLine_img2()
    {
        $res = $this->object->render('<p align="center">[http://blazemeter.com/?utm_source=jmplinnerpages&utm_medium=cpc&utm_content=jmpininnerpgs&utm_campaign=JMeter%2BPlug%2BIn%2BWiki http://apc.kg/img/jpgc/bz_small.jpg]</p>');
        $this->assertEquals('<p><p align="center"><a href="http://blazemeter.com/?utm_source=jmplinnerpages&utm_medium=cpc&utm_content=jmpininnerpgs&utm_campaign=JMeter%2BPlug%2BIn%2BWiki" target="_blank" rel="nofollow"><img src="http://apc.kg/img/jpgc/bz_small.jpg" alt=""/></a></p></p>', $res);
    }

}
