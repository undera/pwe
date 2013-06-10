<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

/**
 * Gestion de l'italic.
 */
class Em extends \WikiRenderer\TagXhtml {
	protected $name = 'em';
	public $beginTag = '\'\'';
	public $endTag = '\'\'';
}

