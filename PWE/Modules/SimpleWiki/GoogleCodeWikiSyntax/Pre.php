<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Block;

class Pre extends Block {

    public $type = 'pre';
    protected $regexp = "";
    protected $_openTag = '<pre>';
    protected $_closeTag = '</pre>';
    private $inBlock = false;

    public function getRenderedLine() {
        $text = $this->_detectMatch[0];
        if ($text == '{{{' || $text == '}}}') {
            return '';
        } else {
            return $text;
        }
    }

    public function detect($string, $inBlock = false) {
        if ($this->inBlock) {
            $this->regexp = '/}}}/';
            if (parent::detect($string, $inBlock)) {
                $this->inBlock = false;
            } else {
                $this->regexp = '/.+/';
                return parent::detect($string, $inBlock);
            }
            return true;
        } else {
            $this->regexp = '/^{{{[^}]*$/';
            if (parent::detect($string, $inBlock)) {
                $this->inBlock = true;
                return true;
            }
            $this->inBlock = false;
            return false;
        }
    }

    public function mustClone() {
        return false;
    }

}

