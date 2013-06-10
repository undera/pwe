<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

/** Gestion de texte monospace. */
class Monospace extends \WikiRenderer\TagXhtml {
	protected $name = 'tt';
	public $beginTag = '##';
	public $endTag = '##';

	/*
	public function isOtherTagAllowed() {
		return false;
	}
	*/
}

