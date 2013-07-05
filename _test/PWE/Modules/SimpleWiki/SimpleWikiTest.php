<?php

namespace PWE\Modules\SimpleWiki;

use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;
use PWE\Exceptions\HTTP3xxException;
use PWE\Modules\SimpleWiki\SimpleWiki;

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

    public function testProcess_main() {
        $PWE = new UnitTestPWECore();
        $PWE->setStructFile(dirname(__FILE__) . '/SimpleWiki.xml');
        $PWE->setURL('/list/');
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

    public function testProcess_google() {
        $PWE = new UnitTestPWECore();
        $PWE->setStructFile(dirname(__FILE__) . '/SimpleWiki.xml');
        $PWE->setURL('/GoogleSyntax/');
        $node = $PWE->getNode();
        $node['!i']['wiki_dir'] = dirname(__FILE__);
        $PWE->setNode($node);
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

    public function testProcess_redir() {
        $PWE = new UnitTestPWECore();
        $PWE->setStructFile(dirname(__FILE__) . '/SimpleWiki.xml');
        $PWE->setURL('/');
        $obj = new SimpleWiki($PWE);
        try {
            $obj->process();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('list/', $e->getMessage());
        }
    }

}
