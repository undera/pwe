<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use WikiRenderer\Block;

class WikiList extends Block
{

    public $type = 'list';
    protected $_previousTag;
    protected $_firstItem = false;
    protected $_firstTagLen;
    protected $regexp = "/^(  )+([\*#]+)\s*(.*)/";
    private $ordered;

    /**
     * test si la chaine correspond au debut ou au contenu d'un bloc
     * @param    string $string
     * @param    bool $inBlock (optional) True if the parser is already in the block.
     * @return    boolean    true: appartient au bloc
     */
    public function detect($string, $inBlock = false)
    {
        if (!preg_match($this->regexp, $string, $this->_detectMatch))
            return (0);
        if ($inBlock !== true && ((substr($string, 0, 2) == '**' && strpos($string, '**', 2) !== false) ||
                (substr($string, 0, 2) == '##' && strpos($string, '##', 2) !== false))
        )
            return (0);
        return (1);
    }

    public function open()
    {
        $this->_previousTag = $this->_detectMatch[1];
        $this->_firstTagLen = strlen($this->_previousTag);
        $this->_firstItem = true;
        if (substr($this->_detectMatch[2], -1, 1) == '#') {
            $this->ordered = true;
            return ("<ol>\n");
        } else {
            $this->ordered = false;
            return ("<ul>\n");
        }
    }

    public function close()
    {
        return $this->ordered ? "</li></ol>\n" : "</li></ul>\n";
    }

    public function getRenderedLine()
    {
        $t = $this->_previousTag;
        $d = strlen($t) - strlen($this->_detectMatch[1]);
        $str = '';
        if ($d > 0) {
            $str .= $this->ordered ? "</li></ol>\n" : "</li></ul>\n";
            $str .= "</li>\n<li>";
            $this->_previousTag = substr($this->_previousTag, 0, -$d);
        } else if ($d < 0) {
            $this->_previousTag .= ""; // FIXME: what was here?
            $str = $this->ordered ? "<ol><li>" : "<ul><li>";
        } else {
            $str = $this->_firstItem ? '<li>' : "</li>\n<li>";
        }
        $this->_firstItem = false;
        return ($str . $this->_renderInlineTag($this->_detectMatch[3]));
    }

}

