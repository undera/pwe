<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

class Underline extends TagXhtml {

    protected $name = 'u';
    public $beginTag = '__';
    public $endTag = '__';

}

