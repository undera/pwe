<?php

namespace PWE\Modules\SimpleWiki;

use PWE\Core\PWELogger;
use PWE\Core\UnitTestPWECore;

class SimpleWikiTest extends \PHPUnit_Framework_TestCase {

    public function testRender() {
        $PWE = new UnitTestPWECore();
        $obj = new SimpleWiki($PWE);
        $res = $obj->renderPage('/home/undera/NetBeansProjects/JMeter-Plugins-Wiki/InterThreadCommunication.wiki');
        PWELogger::debug($res);
    }

    public function testProcess() {
        $PWE = new UnitTestPWECore();
        $PWE->setURL('/');
        $obj = new SimpleWiki($PWE);
        $obj->process();
        $PWE->getContent();
    }

}
