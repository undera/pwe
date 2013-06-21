<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Renderer;

class MonospaceTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Monospace
     */
    protected $object;

    protected function setUp() {
        $this->object = new Renderer(new Config());
    }

    public function test1() {
        $res = $this->object->render("{{{ jmeter.save.saveservice.thread_counts=true }}}");
        $this->assertEquals('<p><tt> jmeter.save.saveservice.thread_counts=true </tt></p>', $res);
    }

    public function test2() {
        $res = $this->object->render("{{{ \${machineName()}_My Threadgroup name }}}");
        $this->assertEquals('<p><tt> ${machineName()}_My Threadgroup name </tt></p>', $res);
    }

}
