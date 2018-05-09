<?php

namespace PWE\Modules\SimpleWiki\GitHubMarkdownSyntax;

use PWE\Modules\PWEConnected;
use WikiRenderer\TagXhtml;

class Link extends TagXhtml
{

    protected $name = 'a';
    public $beginTag = '[';
    public $endTag = ')';
    public $separators = array('](');
    protected $attribute = array('href', '$$',);
    static $img_exts = array('png', 'jpg', 'gif', 'svg');

    public function getContent()
    {
        // management of single parameter
        if ($this->separatorCount == 0) {
            throw new \RuntimeException("Link syntax with single separator is not supported");
            $this->separatorCount = 1;
            $href = $this->wikiContentArr[0];
            list($href, $label, $targetBlank, $nofollow) = $this->config->processLink($href, $this->name);
            $this->contents[1] = $label;
        } else {
            $href_split = explode(' ', $this->wikiContentArr[1]);
            $href = $href_split[0];
            list($href, $label, $targetBlank, $nofollow) = $this->config->processLink($href, $this->name);
            $this->contents[1] = implode($this->separators[0], array_slice($this->contents, 0, 1));
        }

        $ext = strtolower(pathinfo($href, PATHINFO_EXTENSION));
        $parts = explode('.', $this->contents[1]);
        $ext_label = strtolower(end($parts));

        if (in_array($ext, self::$img_exts)) {
            if (in_array($ext_label, self::$img_exts)) {
                $alt = '';
            } else {
                $alt = $this->contents[1];
            }

            if ($this->config instanceof PWEConnected) {
                $node = $this->config->getPWE()->getNode();
                if ((strpos($href, '://') == false && $href[0] != '/') && $node['!i']['img_path_prepend']) {
                    $href = $node['!i']['img_path_prepend'] . $href;
                }
            }

            return '<img src="' . $href . '" alt="' . $alt . '"/>';
        } else {
            if (!strstr($href, '://') && strpos($href, '/') !== 0 && strpos($href, '#') !== 0) {
                $href = '../' . $href;
            }

            $this->wikiContentArr[0] = $href;

            // management of the target
            $targetBlank = isset($targetBlank) ? $targetBlank : $this->config->getParam('targetBlank');
            $nofollow = isset($nofollow) ? $nofollow : $this->config->getParam('nofollow');
            if (!isset($targetBlank) || !isset($nofollow)) {
                // no targetBlank behaviour defined, check the link
                if ((isset($href[0]) && isset($href[1]) && $href[0] === '/' && $href[1] === '/') ||
                    strpos($href, '://') !== false
                ) {
                    if (!isset($targetBlank))
                        $targetBlank = true;
                    if (!isset($nofollow))
                        $nofollow = true;
                }
            }
            if ($targetBlank)
                $this->additionnalAttributes['target'] = '_blank';
            else
                unset($this->additionnalAttributes['target']);
            if ($nofollow)
                $this->additionnalAttributes['rel'] = 'nofollow';
            else
                unset($this->additionnalAttributes['rel']);
            // link generation
            return $this->getContent_copied();
        }
    }

    public function isOtherTagAllowed()
    {
        return false;
    }

    protected function _doEscape($string)
    {
        return $string;
    }

    /*
     * Had to override it from TagXhtml, since it forces htmlspecialchars
     * and breaks links with params
     */

    public function getContent_copied()
    {
        $attr = '';
        $cntattr = count($this->attribute);
        $count = ($this->separatorCount >= $cntattr) ? ($cntattr - 1) : $this->separatorCount;
        $content = '';

        for ($i = 0; $i <= $count; $i++) {
            if (in_array($this->attribute[$i], $this->ignoreAttribute))
                continue;
            if ($this->attribute[$i] != '$$') {
                $attr .= ' ' . $this->attribute[$i] . '="' . $this->wikiContentArr[$i] . '"';
            } else {
                $content = $this->contents[$i];
            }
        }

        foreach ($this->additionnalAttributes as $name => $value) {
            $attr .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
        }

        return '<' . $this->name . $attr . '>' . $content . '</' . $this->name . '>';
    }

}

