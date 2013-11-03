<?php

namespace PWE\Core;

use PWE\Modules\CMDLineModulesManager;
use RuntimeException;

class CMDLinePWECore extends PWECore
{

    public function __construct($registryPath)
    {
        parent::__construct();
        if (!is_file($registryPath)) {
            throw new RuntimeException("Registry file not found: " . $registryPath);
        }
        $this->setXMLDirectory(dirname(__FILE__));
        $this->setTempDirectory('/tmp');
        $mgr = new CMDLineModulesManager($this);
        $mgr->setRegistryFile($registryPath);
        $this->createModulesManager($mgr);
    }

    public function getNode()
    {
        return array('!a' => array('move_to_dir' => '/tmp'));
    }

}

?>