<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

class Strong extends TagXhtml
{

    protected $name = 'strong';
    public $beginTag = '*';
    public $endTag = '*';

}

