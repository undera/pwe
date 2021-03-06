<?php

namespace PWE\Modules\HTMLPage;

use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP4xxException;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Modules\Setupable;
use PWE\Utils\FilesystemHelper;

class HTMLPage extends PWEModule implements Outputable, Setupable
{
    private function isHTMfile($f)
    {
        $file_parts = explode('.', $f);
        $tmp = strtolower(end($file_parts));
        return in_array($tmp, array('htm', 'html'));
    }

    public function process()
    {
        $eg_node = $this->PWE->getNode();
        //var_dump($eg_node);

        if (!isset($eg_node['!a']['src'])) {
            throw new HTTP4xxException("File to display not specified", HTTP4xxException::NOT_FOUND);
        }

        if (!$this->isHTMfile($eg_node['!a']['src'])) {
            throw new HTTP4xxException("File is not HTML: " . $eg_node['!a']['src'], HTTP4xxException::PRECONDITION_FAILED);
        }

        // файл с содержимым
        $src = self::getHTMLDirectory($this->PWE) . '/' . FilesystemHelper::protectAgainsRelativePaths($eg_node['!a']['src']);

        // если таковой есть
        if (is_file($src)) {
            PWELogger::debug("HTML File: %s", $src);
            // показываем его
            $smarty = $this->PWE->getSmarty();
            $smarty->assign('content', file_get_contents($src));
            $smarty->setTemplateFile(__DIR__ . "/HTMLPage.tpl");
            $this->PWE->addContent($smarty);
        } else {
            // иначе пишем во внутренний лог
            // и культурно ругаемся
            PWELogger::warn("File not found: %s", $src);
            throw new HTTP4xxException("This page is not ready yet", HTTP4xxException::NOT_FOUND);
        }
    }

    private static function getHTMLDirectory(PWECore $pwe)
    {
        return $pwe->getDataDirectory() . '/html';
    }

    public static function setup(PWECore $pwe, array &$registerData)
    {
        if (!is_dir(self::getHTMLDirectory($pwe))) {
            PWELogger::info("Creating HTML directory: %s", self::getHTMLDirectory($pwe));
            mkdir(self::getHTMLDirectory($pwe), null, true);
        }
    }

}
