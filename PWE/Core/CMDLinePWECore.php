<?php

namespace PWE\Core;

use PWE\Modules\CMDLineModulesManager;

class CMDLinePWECore extends PWECore
{

    public function __construct()
    {
        parent::__construct();
        $this->setXMLDirectory(__DIR__);
        $this->setTempDirectory('/tmp');
        $mgr = new CMDLineModulesManager($this);
        $this->createModulesManager($mgr);
    }

    public function &getNode()
    {
        return array('!a' => array('move_to_dir' => '/tmp'));
    }
}

