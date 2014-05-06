<?php

namespace PWE\Modules\SmartyPage;

use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP4xxException;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;

class SmartyPage extends PWEModule implements Outputable
{

    public function process()
    {
        $eg_node = $this->PWE->getNode();

        $shown = false;
        foreach ($eg_node['!a'] as $attr => $value) {
            if (strpos($attr, 'tpl') === false) {
                continue;
            }

            if (!is_file($value)) {
                // иначе пишем во внутренний лог
                // и культурно ругаемся
                PWELogger::warn("File not found: %s", $value);
                throw new HTTP4xxException("This page is not ready yet", HTTP4xxException::NOT_FOUND);
            }

            $part = substr($attr, strlen("tpl_"));

            PWELogger::debug("TPL File: %s / %s", $part, $value);
            $smarty = $this->PWE->getSmarty();
            $smarty->setTemplateFile($value);
            $this->PWE->addContent($smarty, $part);
            $shown = true;
        }

        if (!$shown) {
            throw new HTTP4xxException("No tpl files set", HTTP4xxException::NOT_FOUND);
        }

    }
}

?>