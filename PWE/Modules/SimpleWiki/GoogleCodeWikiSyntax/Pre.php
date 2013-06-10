<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Block;

class Pre extends Block {

    public $type = 'pre';
    protected $regexp = "/^\s(.*)/";
    protected $_openTag = '<pre>';
    protected $_closeTag = '</pre>';

    public function getRenderedLine() {
        $text = $this->_detectMatch[1];
        return $this->_renderInlineTag($text);
    }

}

