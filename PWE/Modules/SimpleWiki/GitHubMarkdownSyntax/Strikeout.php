<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\TagXhtml;

class Strikeout extends TagXhtml
{

    protected $name = 's';
    public $beginTag = "~~";
    public $endTag = "~~";

}

