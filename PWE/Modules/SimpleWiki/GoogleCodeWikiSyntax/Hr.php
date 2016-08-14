<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Block;

/**
 * traite les signes de types hr
 */
class Hr extends Block
{
    public $type = 'hr';
    protected $regexp = '/^-{4,}$/';
    protected $_closeNow = true;

    public function getRenderedLine()
    {
        return '<hr />';
    }
}

