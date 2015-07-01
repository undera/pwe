<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\TagXhtml;

class Em extends TagXhtml
{

    protected $name = 'em';
    public $beginTag = "_";
    public $endTag = "_";

}

