<?php

namespace PWE\Modules;

use PWE\Core\PWECore;

/**
 *
 * @author undera
 */
abstract class PWEModule {

    /**
     *
     * @var PWECore engine instance
     */
    protected $PWE;

    public function __construct(PWECore $core) {
        $this->PWE = $core;
    }

    /**
     *
     * @return PWECore
     */
    public function getPWE() {
        return $this->PWE;
    }

    /**
     * Wherever you called with URL params you may get relative link
     * to your base structure node
     * @return string relative path to node base
     */
    protected function getBaseLink() {
        $res = '.';
        $res.=str_repeat("/..", sizeof($this->PWE->getURL()->getParamsAsArray()));
        return $res;
    }

}

?>