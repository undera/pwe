<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use GeSHi;
use WikiRenderer\Block;

/** Gestion des paragraphes de code. */
class Code extends Block
{
    public $type = 'pre';
    protected $isOpen = false;
    /** Nom du langage de programmation */
    private $_programmingLanguage = '';
    /** This object shouldn't be cloned. */
    protected $_mustClone = false;
    /** Raw content of the code block. */
    private $_currentContent = '';
    /** GeSHi object. */
    static private $_geshi = null;
    private $needsClosing = false;

    public static function isMyLine($string)
    {
        return strlen($string) >= 3 && strstr($string, '```') == $string;
    }

    /**
     * Retourne le tag fermant, et positionne le flag interne pour dire qu'on est à l'intérieur d'un bloc stylisé.
     * @return    string    Le tag ouvrant.
     */
    public function open()
    {
        $this->isOpen = true;
        return (null);
    }

    /**
     * Retourne le tag fermant, et positionne le flag interne pour dire qu'on n'est plus à l'intérieur d'un bloc stylisé.
     * @return    string    Le tag fermant.
     */
    public function close()
    {
        $this->isOpen = false;
        $this->_programmingLanguage = str_replace(array('/', '\\', '..'), '', $this->_programmingLanguage);
        $currentContent = $this->_currentContent;
        $this->_currentContent = '';
        // remove the last carriage-return
        $last2 = substr($currentContent, -2);
        if ($last2 === "\r\n" || $last2 == "\n\r")
            $currentContent = substr($currentContent, 0, -2);
        else if (isset($last2[1]) && ($last2[1] == "\r" || $last2[1] == "\n"))
            $currentContent = substr($currentContent, 0, -1);
        // if no programming language was defined, it's a verbatim block
        if (empty($this->_programmingLanguage)) {
            return ('<pre>' . htmlspecialchars($currentContent) . '</pre>');
        }

        // is syntax highlighting disabled?
        if (!$this->engine->getConfig()->getParam('codeSyntaxHighlight') || !class_exists('\GeSHi'))
            return ('<pre><code class="language-' . $this->_programmingLanguage . '">' . htmlspecialchars($currentContent) . '</code></pre>');
        // syntax highlighting
        //if (!isset(self::$_geshi)) {
        self::$_geshi = new GeSHi('', '');
        //}
        self::$_geshi->set_source($currentContent);
        self::$_geshi->set_language($this->_programmingLanguage, true);
        self::$_geshi->enable_classes($this->engine->getConfig()->getParam('codeInlineStyles') ? false : true);
        //self::$_geshi->enable_line_numbers($this->engine->getConfig()->getParam('codeLineNumbers') ? GESHI_NORMAL_LINE_NUMBERS : GESHI_NO_LINE_NUMBERS);
        $result = self::$_geshi->parse_code();

        $start = '<pre class="' . $this->_programmingLanguage . '"';
        if (substr($result, 0, strlen($start)) == $start)
            $result = '<pre class="' . $this->_programmingLanguage . ' language-' . $this->_programmingLanguage . '"' . substr($result, strlen($start));

        $result .= '<style type="text/css">' . self::$_geshi->get_stylesheet(true) . '</style>';
        return ($result);
    }

    /**
     * Retourne la ligne courant, après traitement.
     * @return    string    La ligne courante après traitement.
     */
    public function getRenderedLine()
    {
        return (false);
    }

    /**
     * Détecte si on est au début ou à la fin d'un bloc de code.
     * @param    string $string La chaîne à analyser.
     * @param    bool $inBlock (optional) True if the parser is already in the block.
     * @return    bool    True si le début ou la fin de bloc a été trouvée.
     */
    public function detect($string, $inBlock = false)
    {
        $this->_detectMatch = false;
        if ($this->isOpen) {
            if (self::isMyLine($string)) {
                $this->needsClosing = true;
                return true;
            }

            if (!$this->needsClosing) {
                $this->_currentContent .= $string . "\n";
                return true;
            }

            return false;
        } else if (self::isMyLine($string)) {
            $this->needsClosing = false;
            $this->_programmingLanguage = trim(substr($string, 3));
            return true;
        }
        $this->isOpen = false;
        return false;
    }
}

