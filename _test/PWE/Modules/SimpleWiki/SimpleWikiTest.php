<?php

namespace PWE\Modules\SimpleWiki;

use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;

class SimpleWikiTest extends \PHPUnit_Framework_TestCase {

    public function testRender() {
        $PWE = new UnitTestPWECore();
        $obj = new SimpleWiki($PWE);
        // Took it from http://code.google.com/p/support/source/browse/wiki/WikiSyntax.wiki
        $res = $obj->renderPage(dirname(__FILE__) . "/GoogleSyntax.wiki");
        PWELogger::debug($res);
    }

    // opened issue for skriv: https://github.com/Amaury/SkrivMarkup/issues/20
    public function testRender_tableBug1() {
        $PWE = new UnitTestPWECore();
        $obj = new SimpleWiki($PWE);
        $res = $obj->renderPage(dirname(__FILE__) . "/TableBug1.wiki");
        PWELogger::debug($res);
    }

    public function testProcess() {
        $PWE = new UnitTestPWECore();
        $PWE->setStructFile(dirname(__FILE__) . '/SimpleWiki.xml');
        $PWE->setURL('/GoogleSyntax.wiki');
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

}
