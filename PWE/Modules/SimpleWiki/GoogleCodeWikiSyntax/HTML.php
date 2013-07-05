<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Tag;

class HTML extends Tag {

    protected $name = 'html';
    public $beginTag = '<';
    public $endTag = '>';
    protected $attribute = array('$$',);
    public static $available_tags = array('i', 'b', 'font', 'sup', 'p', 'br', "table", "tr", "td");

    public function getContent() {
        $tag = str_replace('/', '', strtolower(reset(explode(' ', $this->contents[0]))));
        if (in_array($tag, self::$available_tags)) {
            return $this->beginTag . implode('', $this->contents) . $this->endTag;
        } else {
            return '&lt;' . parent::getContent() . '&gt;';
        }
    }

}

