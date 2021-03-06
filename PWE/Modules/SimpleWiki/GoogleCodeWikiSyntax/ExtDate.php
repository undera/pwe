<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\TagXhtml;

/** Extension for date manipulation. */
class ExtDate extends TagXhtml
{
    protected $name = 'image';
    public $beginTag = '<<date';
    public $endTag = '>>';
    protected $attribute = array('format', 'time');
    public $separators = array('|');

    public function getContent()
    {
        if (!isset($this->wikiContentArr[1]))
            return "";
        $format = $this->wikiContentArr[1];
        if (isset($this->wikiContentArr[2])) {
            $time = $this->wikiContentArr[2];
            if (ctype_digit($time))
                return (strftime($format, $time));
            return (strftime($format, strtotime($time)));
        }
        return (strftime($format));
    }
}

