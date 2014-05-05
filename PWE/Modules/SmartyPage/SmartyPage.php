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
                PWELogger::warning("File not found: " . $value);
                throw new HTTP4xxException("This page is not ready yet", HTTP4xxException::NOT_FOUND);
            }

            $part = substr($attr, 3);

            PWELogger::debug("TPL File: $part / $value");
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