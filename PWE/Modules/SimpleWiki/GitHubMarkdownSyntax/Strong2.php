<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\TagXhtml;

class Strong2 extends TagXhtml
{

    protected $name = 'strong';
    public $beginTag = "**";
    public $endTag = "**";

}

