<?php

namespace PWE\Modules;

use PWE\Core\PWELogger;

class CMDLineModulesManager extends PWEModulesManager
{
    protected function loadRegistry()
    {
        if (is_file($this->registryFile)) {
            parent::loadRegistry();
        } else {
            PWELogger::debug("File not found: %s", $this->registryFile);
        }
    }

}

?>