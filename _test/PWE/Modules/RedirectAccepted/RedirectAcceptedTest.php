<?php

namespace PWE\Modules\RedirectAccepted;

require_once dirname(__FILE__) . '/../../../PWEUnitTests.php';

use PWE\Core\UnitTestPWECore;
use PWE\Exceptions\HTTP3xxException;

class RedirectAcceptedTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var RedirectAccepted
     */
    protected $object;

    protected function setUp() {
        $this->object = new RedirectAccepted(new UnitTestPWECore);
    }

    public function testProcess() {
        $this->object->getPWE()->setStructFile(dirname(__FILE__) . '/redirect.xml');
        $this->object->getPWE()->setURL('/param/');
        $_GET['getparam'] = 'test';
        try {
            $this->object->process();
            $this->fail();
        } catch (HTTP3xxException $e) {
            $this->assertEquals("/redir/param/?getparam=test", $e->getMessage());
        }
    }

}

?>
