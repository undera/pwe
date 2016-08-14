<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Renderer;

class FileDownloadTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Renderer
     */
    protected $object;

    protected function setUp()
    {
        $cnf = new Config();
        $pwe = new \PWE\Core\UnitTestPWECore();
        $pwe->setXMLDirectory(__DIR__);
        $cnf->setPWE($pwe);
        $pwe->setURL('/');
        $this->object = new Renderer($cnf);
    }

    public function testGetRenderedLine_anchored()
    {
        $res = $this->object->render("<download:test.txt;some descr>");
        //$this->assertEquals('', $res);
    }

}
