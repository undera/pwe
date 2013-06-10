<?php

namespace PWE\Modules\SimpleWiki;

use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Lib\Smarty\SmartyWrapper;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use WikiRenderer\Markup\DokuHtml\Config;
use WikiRenderer\Renderer;

class SimpleWiki extends PWEModule implements Outputable {

    public function process() {
        $node = $this->PWE->getNode();
        if (!is_dir($node['!i']['wiki_dir'])) {
            throw new HTTP5xxException("Not configured wiki source dir");
        }

        $args = $this->PWE->getURL()->getParamsAsArray();
        if (!$args) {
            throw new HTTP4xxException("No page requested");
        }

        $file = $node['!i']['wiki_dir'] . '/' . $args[0];
        if (!is_file($file)) {
            PWELogger::error("Not found wiki file: $file");
            throw new HTTP4xxException("Wiki page not found", HTTP4xxException::NOT_FOUND);
        }
        
        $smarty = new SmartyWrapper($this->PWE);
        $smarty->setTemplateFile(dirname(__FILE__) . '/wiki.tpl');
        $smarty->assign('content', $this->renderPage($file));
        $this->PWE->addContent($smarty);
    }

    public function renderPage($page) {
        $w = new Renderer(new Config());
        $text = file_get_contents($page);
        $res = $w->render($text);
        return $res;
    }

}

?>