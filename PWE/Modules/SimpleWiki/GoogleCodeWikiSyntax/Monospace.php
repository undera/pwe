<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

class Monospace extends TagXhtml
{

    protected $name = 'tt';
    public $beginTag = '{{{';
    public $endTag = '}}}';

    public function isOtherTagAllowed()
    {
        return false;
    }


}

