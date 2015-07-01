<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\TagXhtml;

class Inliner extends TagXhtml
{

    protected $name = 'tt';
    public $beginTag = "`";
    public $endTag = "`";
}

