<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

/**
 * Gestion du texte en exposant.
 */
class Superscript extends \WikiRenderer\TagXhtml
{
    protected $name = 'sup';
    public $beginTag = '^';
    public $endTag = '^';
}

