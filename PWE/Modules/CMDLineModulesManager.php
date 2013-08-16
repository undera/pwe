<?php

namespace PWE\Modules;

use PWE\Core\PWECore;
use PWE\Core\PWELogger;

class CMDLineModulesManager extends PWEModulesManager {

    public function __construct(PWECore $pwe, $regFile) {
        parent::__construct($pwe, $regFile);
    }

    protected function loadRegistry() {
        if (is_file($this->registryFile)) {
            parent::loadRegistry();
        } else {
            PWELogger::debug("File not found: " . $this->registryFile);
        }
    }

}

?>