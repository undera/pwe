<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Renderer;

class FileDownloadTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Renderer
     */
    protected $object;

    protected function setUp() {
        $this->object = new Renderer(new Config());
    }

    public function testGetRenderedLine_anchored() {
        $res = $this->object->render("<download:test.txt;some descr>");
        $this->assertEquals('', $res);
    }

}
