<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Block;

class Pragma extends Block {

    public $type = 'comment';
    protected $regexp = "/^(#(labels|summary)\s.*)$/";
    protected $_openTag = '<!--';
    protected $_closeTag = '-->';
    private $seen = false;

    public function getRenderedLine() {
        $text = $this->_detectMatch[0];
        if (!strstr($text, '{{{') && !strstr($text, '}}}')) {
            return $text;
        } else {
            return "";
        }
    }

    public function detect($string, $inBlock = false) {
        if ($this->seen) {
            return false;
        }

        if (strpos($string, '#labels ') === 0 || strpos($string, '#summary ') === 0) {
            $this->seen = true;
            $this->_detectMatch = array($string);
            return true;
        }
        return false;
    }

}

