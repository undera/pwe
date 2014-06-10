<?php

namespace PWE\Modules\SimpleWiki;

use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;

class SimpleWikiTest extends \PHPUnit_Framework_TestCase
{

    public function testRender()
    {
        $PWE = new UnitTestPWECore();
        $obj = new SimpleWiki($PWE);
        // Took it from http://code.google.com/p/support/source/browse/wiki/WikiSyntax.wiki
        $res = $obj->renderPage(__DIR__ . "/GoogleSyntax.wiki");
        PWELogger::debug($res);
    }

    // opened issue for skriv: https://github.com/Amaury/SkrivMarkup/issues/20
    public function testRender_tableBug1()
    {
        $PWE = new UnitTestPWECore();
        $obj = new SimpleWiki($PWE);
        $res = $obj->renderPage(__DIR__ . "/TableBug1.wiki");
        PWELogger::debug($res);
    }

    public function testProcess_main()
    {
        $PWE = new UnitTestPWECore();
        file_put_contents($PWE->getTempDirectory() . '/start.wiki', "");
        $PWE->setStructFile(__DIR__ . '/SimpleWiki.xml');
        $PWE->setURL('/list/');
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

    public function testProcess_main_dir_ok()
    {
        $PWE = new UnitTestPWECore();
        mkdir($PWE->getTempDirectory() . '/subdir');
        file_put_contents($PWE->getTempDirectory() . '/subdir/test.wiki', "");
        $PWE->setStructFile(__DIR__ . '/SimpleWiki.xml');
        $PWE->setURL('/subdir:test/');
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

    public function testProcess_main_dir_explot()
    {
        $PWE = new UnitTestPWECore();
        mkdir($PWE->getTempDirectory() . '/subdir');
        file_put_contents($PWE->getTempDirectory() . '/subdir/test.wiki', "");
        $PWE->setStructFile(__DIR__ . '/SimpleWiki.xml');
        $PWE->setURL('/subdir:..:test/');
        $obj = new SimpleWiki($PWE);
        try {
            $obj->process();
            $this->fail();
        } catch (HTTP4xxException $e) {
        }
    }


    public function testProcess_google()
    {
        $PWE = new UnitTestPWECore();
        $PWE->setStructFile(__DIR__ . '/SimpleWiki.xml');
        $PWE->setURL('/GoogleSyntax/');
        $node = $PWE->getNode();
        $node['!i']['wiki_dir'] = __DIR__;
        $PWE->setNode($node);
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

    public function testProcess_redir()
    {
        $PWE = new UnitTestPWECore();
        $PWE->setStructFile(__DIR__ . '/SimpleWiki.xml');
        $PWE->setURL('/');
        $obj = new SimpleWiki($PWE);
        try {
            $obj->process();
        } catch (HTTP3xxException $e) {
            $this->assertEquals('list/', $e->getMessage());
        }
    }

}
