<?php

namespace PWE\Core;

use PWE\Modules\CMDLineModulesManager;
use RuntimeException;

class CMDLinePWECore extends PWECore {

    public function __construct($registryPath) {
        parent::__construct();
        if (!is_file($registryPath)) {
            throw new RuntimeException("Registry file not found: " . $registryPath);
        }
        $this->setXMLDirectory(dirname(__FILE__));
        $this->setTempDirectory('/tmp');
        $this->createModulesManager(new CMDLineModulesManager($this, $registryPath));
    }

    public function getNode() {
        return array('!a' => array('move_to_dir' => '/tmp'));
    }

}

?>