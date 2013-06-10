<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

class Abbr extends TagXhtml {

    protected $name = 'abbr';
    public $beginTag = '??';
    public $endTag = '??';
    protected $attribute = array('$$', 'title');
    public $separators = array('|');

}

