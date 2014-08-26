<?php

namespace PWE\Modules\SimpleWiki;

use GlobIterator;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Lib\Smarty\SmartyWrapper;
use PWE\Modules\Outputable;
use PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax\Config;
use PWE\Modules\WebPWEModule;
use WikiRenderer\Renderer;

class SimpleWiki extends WebPWEModule implements Outputable
{

    private $config;

    public function __construct(PWECore $core)
    {
        parent::__construct($core);
        $this->config = new Config();
        $this->config->setPWE($core);
    }

    public function process()
    {
        $node = $this->PWE->getNode();
        $dir = $node['!i']['wiki_dir'];
        $start_page = $node['!i']['start_page'] ? $node['!i']['start_page'] : "list";

        if ($dir[0] != DIRECTORY_SEPARATOR) {
            $dir = $this->PWE->getDataDirectory() . '/' . $dir;
        }

        if (!is_dir($dir)) {
            throw new HTTP5xxException("Not configured wiki source dir, or it does not exists");
        }

        PWELogger::debug("Wiki dir: %s", $dir);

        $args = $this->PWE->getURL()->getParamsAsArray();
        if (!$args) {
            throw new HTTP3xxException($start_page . "/");
        } elseif ($args[0] == 'list') {
            $files = new GlobIterator($dir . '/*.wiki');
            $text = "";
            foreach ($files as $file) {
                $f = pathinfo($file, PATHINFO_FILENAME);
                $text .= "  # [$f " . $f . "]\n";
            }
            $contents = $this->getRenderer()->render($text);
        } else {
            if (pathinfo($args[0], PATHINFO_EXTENSION) != 'wiki') {
                $args[0] .= '.wiki';
            }

            $file = $dir . '/' . str_replace(':', DIRECTORY_SEPARATOR, $args[0]);
            $real_file = realpath($file);
            $pos = strpos($real_file, realpath($dir) . DIRECTORY_SEPARATOR);
            PWELogger::debug("Test path for sanity: %s ; %s ; %s", $file, $real_file, $pos);
            if ($pos === false) {
                PWELogger::warn("Possible injection attempt: %s", $file);
                throw new HTTP4xxException("Wrong wiki page specified", HTTP4xxException::NOT_FOUND);
            }

            PWELogger::info("Wiki file to show: %s", $file);

            if (!is_file($file)) {
                PWELogger::error("Not found wiki file: %s", $file);
                throw new HTTP4xxException("Wiki page not found", HTTP4xxException::NOT_FOUND);
            }
            $contents = $this->renderPage($file);
        }
        $smarty = new SmartyWrapper($this->PWE);
        $smarty->setTemplateFile(__DIR__ . '/wiki.tpl');
        $smarty->assign('content', $contents);

        $sidebar = $this->config->getToc();
        if (is_file($dir . '/Sidebar.wiki')) {
            $sidebar .= $this->renderPage($dir . '/Sidebar.wiki');
        }
        $smarty->assign("sidebar", $sidebar);

        $this->PWE->addContent($smarty);
    }

    public function renderPage($page)
    {
        $text = file_get_contents($page);
        return $this->getRenderer()->render($text);
    }

    /**
     *
     * @return Renderer
     */
    protected function getRenderer()
    {
        $w = new Renderer($this->config);
        return $w;
    }

}

?>