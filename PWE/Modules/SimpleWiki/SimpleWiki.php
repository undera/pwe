<?php

namespace PWE\Modules\SimpleWiki;

use GlobIterator;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Lib\Smarty\SmartyWrapper;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax\Config;
use WikiRenderer\Renderer;

class SimpleWiki extends PWEModule implements Outputable {

    public function process() {
        $node = $this->PWE->getNode();
        if (!is_dir($node['!i']['wiki_dir'])) {
            throw new HTTP5xxException("Not configured wiki source dir");
        }

        PWELogger::debug("Wiki dir: " . $node['!i']['wiki_dir']);

        $args = $this->PWE->getURL()->getParamsAsArray();
        if (!$args) {
            throw new HTTP3xxException("Main/");
        } elseif ($args[0] == 'Main') {
            $files = new GlobIterator($node['!i']['wiki_dir'] . '/*.wiki');
            $text = "";
            foreach ($files as $file) {
                $f = end(explode('/', $file->getFilename()));
                $f = reset(explode('.', $f));
                $text.= "  # [$f " . $f . "]\n";
            }
            $contents = $this->getRenderer()->render($text);
        } else {
            $file = $node['!i']['wiki_dir'] . '/' . $args[0];
            if (end(explode('.', $file)) != 'wiki') {
                $file.='.wiki';
            }

            if (!is_file($file)) {
                PWELogger::error("Not found wiki file: $file");
                throw new HTTP4xxException("Wiki page not found", HTTP4xxException::NOT_FOUND);
            }
            $contents = $this->renderPage($file);
        }
        $smarty = new SmartyWrapper($this->PWE);
        $smarty->setTemplateFile(dirname(__FILE__) . '/wiki.tpl');
        $smarty->assign('content', $contents);
        if (is_file($node['!i']['wiki_dir'] . '/Sidebar.wiki')) {
            $smarty->assign("sidebar", $this->renderPage($node['!i']['wiki_dir'] . '/Sidebar.wiki'));
        }

        $this->PWE->addContent($smarty);
    }

    public function renderPage($page) {
        $text = file_get_contents($page);
        return $this->getRenderer()->render($text);
    }

    /**
     * 
     * @return Renderer
     */
    protected function getRenderer() {
        $w = new Renderer(new Config());
        return $w;
    }

}

?>