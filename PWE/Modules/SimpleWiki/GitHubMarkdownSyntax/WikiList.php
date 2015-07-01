<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\Block;

class WikiList extends Block
{

    public $type = 'list';
    protected $_previousTag;
    protected $_firstItem = false;
    protected $_firstTagLen;
    protected $regexp = self::REGEXP;
    private $ordered;
    private $level;
    const REGEXP = '/^(\s*)([0-9]+\.|\-|\*|\+)\s*([^*]*)/';

    /**
     * test si la chaine correspond au debut ou au contenu d'un bloc
     * @param    string $string
     * @param    bool $inBlock (optional) True if the parser is already in the block.
     * @return    boolean    true: appartient au bloc
     */
    public function detect($string, $inBlock = false)
    {
        if (!preg_match($this->regexp, $string, $this->_detectMatch)) {
            return (0);
        }

        if ($inBlock) {

        }

        return (1);
    }

    public function open()
    {
        $this->_previousTag = $this->_detectMatch[1];
        $this->_firstTagLen = strlen($this->_previousTag);
        $this->_firstItem = true;
        $char = substr($this->_detectMatch[2], -1, 1);
        if (!in_array($char, array('*', '-', '+'))) {
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
        $indent = strlen($this->_detectMatch[1]) - strlen(ltrim($this->_detectMatch[1]));
        $str = '';
        if ($indent < $this->level) {
            $str .= $this->close();
            $str .= "</li>\n<li>";
        } else if ($indent > $this->level) {
            $str = $this->open().'<li>';
        } else {
            $str = $this->_firstItem ? '<li>' : "</li>\n<li>";
        }
        $this->_firstItem = false;

        $this->level = $indent;
        return ($str . $this->_renderInlineTag($this->_detectMatch[3]));
    }

}

