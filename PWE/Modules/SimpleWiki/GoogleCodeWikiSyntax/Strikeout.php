<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

class Strikeout extends TagXhtml
{

    protected $name = 's';
    public $beginTag = '~~';
    public $endTag = '~~';

}

