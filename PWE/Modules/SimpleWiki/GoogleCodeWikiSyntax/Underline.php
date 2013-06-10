<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

/** Gestion du texte souligné. */
class Underline extends \WikiRenderer\TagXhtml {
	protected $name = 'u';
	public $beginTag = '__';
	public $endTag = '__';
}

