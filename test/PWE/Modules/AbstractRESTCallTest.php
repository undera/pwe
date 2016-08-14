<?php

namespace PWE\Modules;


use PWE\Core\UnitTestPWECore;

class AbstractRESTCallTest extends \PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $core = new UnitTestPWECore();
        $core->setURL('/');
        $obj = new AbstractRESTCallTestImpl($core);
        $obj->process();
    }

    public function testAll()
    {
        foreach (array('DELETE', 'POST', 'PUT', 'PATCH') as $method) {
            $_SERVER['REQUEST_METHOD'] = $method;
            $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
            $core = new UnitTestPWECore();
            $core->setURL('/123/');
            $obj = new AbstractRESTCallTestImpl($core);
            $obj->process();
            $this->assertNotEmpty($core->getContent());
        }
    }
}

class AbstractRESTCallTestImpl extends AbstractRESTCall
{
}