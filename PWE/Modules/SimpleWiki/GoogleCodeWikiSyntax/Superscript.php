<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;
use WikiRenderer\TagXhtml;

/**
 * Gestion du texte en exposant.
 */
class Superscript extends TagXhtml
{
    protected $name = 'sup';
    public $beginTag = '^';
    public $endTag = '^';
}

