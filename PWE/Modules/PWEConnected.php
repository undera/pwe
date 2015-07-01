<?php
namespace PWE\Modules;

use PWE\Core\PWECore;

interface PWEConnected
{

    /**
     *
     * @return PWECore
     */
    public function getPWE();

    public function setPWE(PWECore $core);
}