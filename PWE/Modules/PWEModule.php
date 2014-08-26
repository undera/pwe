<?php

namespace PWE\Modules;

use PWE\Core\PWECore;

abstract class PWEModule
{

    /**
     *
     * @var PWECore engine instance
     */
    protected $PWE;

    public function __construct(PWECore $core)
    {
        $this->PWE = $core;
    }

    /**
     *
     * @return PWECore
     */
    public function getPWE()
    {
        return $this->PWE;
    }

}

?>