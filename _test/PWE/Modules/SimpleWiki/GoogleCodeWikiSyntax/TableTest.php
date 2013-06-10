<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax\Table;
use WikiRenderer\Renderer;

class TableTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Table
     */
    protected $object;

    protected function setUp() {
        $this->object = new TableEmul(new Renderer());
    }

    public function testOpen() {
        $this->object->open();
    }

    public function testGetRenderedLine() {
        $this->object->detect("|| *Pragma*   || *Value*  ||");
        $this->object->getRenderedLine();
    }

}

class TableEmul extends Table {

    public function detect($string, $inBlock = false) {
        $this->_detectMatch = array($string);
    }

}