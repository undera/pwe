<?php

namespace PWE\Modules;

/**
 * Description of TestPWEModulesManager
 *
 * @author undera
 */
class TestPWEModulesManager extends PWEModulesManager {

    public function setModuleSettings($moduleName, $settings) {
        $node = &$this->getModuleNode($moduleName);
        $node['!c'] = $settings;
        $this->saveRegistry();
    }

}

?>
