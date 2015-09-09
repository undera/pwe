<?php

namespace PWE\Modules\SimpleWiki;

use GlobIterator;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Lib\Smarty\SmartyWrapper;
use PWE\Modules\BreadcrumbsGenerator;
use PWE\Modules\Outputable;
use PWE\Modules\PWEConnected;
use PWE\Modules\PWEModule;
use PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax\Config;
use WikiRenderer\Renderer;

class SimpleWiki extends PWEModule implements Outputable, BreadcrumbsGenerator
{

    private $config;

    public function process()
    {
        $node = $this->PWE->getNode();
        $class = $node['!i']['syntax'] ?: get_class(new Config());
        $this->config = new $class();
        if ($this->config instanceof PWEConnected) {
            $this->config->setPWE($this->PWE);
        }

        list($dir, $ext) = $this->getDirAndExt($node);

        $start_page = $node['!i']['start_page'] ?: "list";
        $args = $this->PWE->getURL()->getParamsAsArray();
        if (!$args) {
            throw new HTTP3xxException($start_page . "/");
        } elseif ($args[0] == 'list') {
            $contents = $this->getListedPages($dir, $ext);
        } else {
            $contents = $this->getRenderedPage($args, $ext, $dir);
        }

        $this->processSmarty($contents, $dir, $ext);
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

    /**
     * @param $dir
     * @param $ext
     * @return string
     */
    private function getListedPages($dir, $ext)
    {
        $files = new GlobIterator($dir . '/*' . $ext);
        $text = "";
        foreach ($files as $file) {
            $f = pathinfo($file, PATHINFO_FILENAME);
            $text .= "  # [$f " . $f . "]\n";
        }
        $contents = $this->getRenderer()->render($text);
        return $contents;
    }

    /**
     * @param $args
     * @param $ext
     * @param $dir
     * @return string
     */
    private function getRenderedPage($args, $ext, $dir)
    {
        if (pathinfo($args[0], PATHINFO_EXTENSION) != $ext) {
            $args[0] .= '.' . $ext;
        } else {
            throw new HTTP3xxException(substr($args[0], 0, strlen($args[0]) - strlen($ext) - 1));
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
        return $contents;
    }

    /**
     * @param $node
     * @return array
     */
    private function getDirAndExt($node)
    {
        $dir = $node['!i']['wiki_dir'];
        $ext = $node['!i']['wiki_file_ext'] ?: "wiki";

        if ($dir[0] != DIRECTORY_SEPARATOR) {
            $dir = $this->PWE->getDataDirectory() . '/' . $dir;
        }

        PWELogger::debug("Wiki dir: %s", $dir);
        if (!is_dir($dir)) {
            throw new HTTP5xxException("Not configured wiki source dir, or it does not exists");
        }

        PWELogger::debug("Wiki dir: %s", $dir);
        return array($dir, $ext);
    }

    /**
     * @param $contents
     * @param $dir
     * @param $ext
     */
    private function processSmarty($contents, $dir, $ext)
    {
        $node = $this->PWE->getNode();
        $tpl = $node['!i']['layout_tpl'] ?: __DIR__ . '/wiki.tpl';

        $smarty = new SmartyWrapper($this->PWE);
        $smarty->setTemplateFile($tpl);
        $smarty->assign('content', $contents);

        if ($this->config instanceof TOCProvider) {
            $sidebar = $this->config->getToc();
        } else {
            $sidebar = "";
        }
        if (is_file($dir . '/Sidebar.' . $ext)) {
            $sidebar .= $this->renderPage($dir . '/Sidebar.' . $ext);
        }
        $smarty->assign("sidebar", $sidebar);

        $this->PWE->addContent($smarty);
    }

    public function generateBreadcrumbs()
    {
        $params = $this->PWE->getURL()->getParamsAsArray();
        $res = array();
        $res[] = array('selected' => 1, '!a' => array(
            'link' => "../" . $params[0],
            'title' => $params[0]
        ));
        return $res;
    }
}
