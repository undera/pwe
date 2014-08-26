<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;
use WikiRenderer\TagXhtml;

/**
 * Gestion du texte en indice.
 */
class Subscript extends TagXhtml
{
    protected $name = 'sub';
    public $beginTag = ',,';
    public $endTag = ',,';
}

