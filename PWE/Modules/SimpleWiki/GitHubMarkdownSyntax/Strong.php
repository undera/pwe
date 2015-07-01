<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\TagXhtml;

class Strong extends TagXhtml
{

    protected $name = 'strong';
    public $beginTag = "__";
    public $endTag = "__";

}

