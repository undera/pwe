<?php

namespace PWE\Core;

use PWE\Modules\PWEModulesManager;

interface PWECore
{

    /**
     * @return PWEModulesManager
     */
    public function getModulesManager();

}

?>