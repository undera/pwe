<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Block;

class Table extends Block
{

    public $type = 'table';
    protected $regexp = "/^(!!|\|\|) ?(.*)/";
    protected $_openTag = '<table class="bordered" border="1">';
    protected $_closeTag = '</table>';
    protected $_colcount = 0;

    public function open()
    {
        $this->_colcount = 0;
        return ($this->_openTag);
    }

    public function getRenderedLine()
    {
        $str = '';
        $text = ' ' . $this->_detectMatch[0];
        $prevPos = 0;
        $prevType = '';
        $loop = true;
        $posTd = false;
        while ($loop) {
            if ($prevPos >= strlen($text) || (($posTh = strpos($text, '!!', $prevPos)) === false &&
                    ($posTd = strpos($text, '||', $prevPos)) === false)
            ) {
                $posTh = false;
                $posTd = strlen($text);
                $loop = false;
            }
            if ($posTh === false || (is_int($posTd) && $posTd < $posTh)) {
                $pos = $posTd;
                $type = 'td';
            } else {
                $pos = $posTh;
                $type = 'th';
            }
            if ($prevPos) {
                $cell = substr($text, $prevPos, $pos - $prevPos);
                $str .= "<$prevType>" . $this->_renderInlineTag(trim($cell)) . "</$prevType>";
            }
            $prevPos = $pos + 3;
            $prevType = $type;
        }
        return ("<tr>$str</tr>");
    }

}

