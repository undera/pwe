<?php

namespace PWE\Modules;


use PWE\Core\WebPWECore;

abstract class WebPWEModule extends PWEModule
{
    /**
     * @var WebPWECore
     */
    protected $PWE;

    public function __construct(WebPWECore $core)
    {
        parent::__construct($core);
        $this->PWE = $core;
    }

    /**
     * Wherever you called with URL params you may get relative link
     * to your base structure node
     * @return string relative path to node base
     */
    protected function getBaseLink()
    {
        $res = '.';
        $res .= str_repeat("/..", sizeof($this->PWE->getURL()->getParamsAsArray()));
        return $res;
    }
} 