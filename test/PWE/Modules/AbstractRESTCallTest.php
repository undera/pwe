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
}

class AbstractRESTCallTestImpl extends AbstractRESTCall
{
}