<?php

namespace PWE\Modules;

use PWE\Core\PWELogger;
use PWE\Core\PWECore;

class CMDLineModulesManager extends PWEModulesManager {

    public function __construct(PWECore $pwe, $regFile) {
        parent::__construct($pwe, $regFile);
    }

    protected function loadRegistry() {
        if (is_file($this->registryFile)) {
            parent::loadRegistry();
        } else {
            PWELogger::warning("File not found: " . $this->registryFile);
        }
    }

}

?>