<?php

namespace PWE\Modules;

/**
 * Defines PWEModule as accessible from Web
 * @author undera
 */
interface Outputable
{

    /**
     * @return void
     */
    public function process();
}

?>