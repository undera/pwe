<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

class Em extends TagXhtml
{

    protected $name = 'em';
    public $beginTag = "_";
    public $endTag = "_";

}

