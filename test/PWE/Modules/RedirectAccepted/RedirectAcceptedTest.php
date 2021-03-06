<?php

namespace PWE\Modules\RedirectAccepted;

require_once __DIR__ . '/../../../PWEUnitTests.php';

use PWE\Core\UnitTestPWECore;
use PWE\Exceptions\HTTP3xxException;

class RedirectAcceptedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RedirectAccepted
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new RedirectAccepted(new UnitTestPWECore());
    }

    public function testProcess()
    {
        $this->object->getPWE()->setStructFile(__DIR__ . '/redirect.xml');
        $this->object->getPWE()->setURL('/param/');
        $_GET['getparam'] = 'test';
        try {
            $this->object->process();
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/redir/param/?getparam=test", $e->getMessage());
        }
    }

    public function testProcess_no_slash()
    {
        $this->object->getPWE()->setStructFile(__DIR__ . '/redirect.xml');
        $this->object->getPWE()->setURL('/param_no_slash/test/');
        $_GET['getparam'] = 'test';
        try {
            $this->object->process();
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/redir2/test?getparam=test", $e->getMessage());
        }
    }

}
