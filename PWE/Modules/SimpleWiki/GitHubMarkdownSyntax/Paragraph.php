<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use WikiRenderer\Block;

/** Management of paragraph blocks of text. */
class Paragraph extends Block
{

    public $type = 'p';
    protected $_openTag = '<p>';
    protected $_closeTag = '</p>';

    /** Attribute used to manage carriage-returns inside paragraphs. */
    private $_firstLine = true;

    /**
     * Detection of paragraphs.
     * @param    string $string Line of text.
     * @param    bool $inBlock (optional) True if the parser is already in the block.
     * @return    bool    True if it's a paragraph.
     */
    public function detect($string, $inBlock = false)
    {
        $strim = trim($string);

        if ($inBlock) {
            return (false);
        }

        if (empty($strim)) {
            return (false);
        }

        if (preg_match(WikiList::REGEXP, $string)) {
            return (false);
        }

        if (Code::isMyLine($string)) {
            return (false);
        }

        $this->_detectMatch = array($string, $string);
        return (true);
    }

    /**
     * Rendering of the text insed a paragraph.
     * @param    string $string Text to render.
     * @return    string    Rendered result.
     */
    protected function _renderInlineTag($string)
    {
        $string = $this->engine->inlineParser->parse($string);
        // handling of carriage-returns inside paragraphs
        $string = (!$this->_firstLine) ? " $string" : $string;
        $this->_firstLine = false;
        return ($string);
    }

}

