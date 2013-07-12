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

    private $config;

    public function __construct(\PWE\Core\PWECore $core) {
        parent::__construct($core);
        $this->config = new Config();
    }

    public function process() {
        $node = $this->PWE->getNode();
        $dir = $node['!i']['wiki_dir'];
        $start_page = $node['!i']['start_page'] ? $node['!i']['start_page'] : "list";

        if ($dir[0] != DIRECTORY_SEPARATOR) {
            $dir = $this->PWE->getDataDirectory() . '/' . $dir;
        }

        if (!is_dir($dir)) {
            throw new HTTP5xxException("Not configured wiki source dir, or it does not exists");
        }

        PWELogger::debug("Wiki dir: " . $dir);

        $args = $this->PWE->getURL()->getParamsAsArray();
        if (!$args) {
            throw new HTTP3xxException($start_page . "/");
        } elseif ($args[0] == 'list') {
            $files = new GlobIterator($dir . '/*.wiki');
            $text = "";
            foreach ($files as $file) {
                $f = end(explode('/', $file->getFilename()));
                $f = reset(explode('.', $f));
                $text.= "  # [$f " . $f . "]\n";
            }
            $contents = $this->getRenderer()->render($text);
        } else {
            $file = $dir . '/' . $args[0];
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

        $sidebar = $this->config->getToc();
        if (is_file($dir . '/Sidebar.wiki')) {
            $sidebar.=$this->renderPage($dir . '/Sidebar.wiki');
        }
        $smarty->assign("sidebar", $sidebar);

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
        $w = new Renderer($this->config);
        return $w;
    }

}

?>