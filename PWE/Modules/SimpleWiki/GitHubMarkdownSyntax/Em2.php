<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\TagXhtml;

class Em2 extends TagXhtml
{

    protected $name = 'em';
    public $beginTag = "*";
    public $endTag = "*";

}

