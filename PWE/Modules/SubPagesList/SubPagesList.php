<?php

namespace PWE\Modules\SubPagesList;

use PWE\Modules\Outputable;
use PWE\Modules\WebPWEModule;

class SubPagesList extends WebPWEModule implements Outputable
{
    const DEFAULT_COLUMNS = 3;

    public function process()
    {
        $smarty = $this->PWE->getSmarty();
        $node = $this->PWE->getNode();
        $smarty->assign('subpages', $node['!c']['url']);
        $smarty->assign('format', $node['!a']['format']);
        $smarty->assign('columns', $node['!a']['columns'] ? $node['!a']['columns'] : self::DEFAULT_COLUMNS);
        $smarty->setTemplateFile(__DIR__ . "/SubPagesList.tpl");
        $this->PWE->addContent($smarty);
    }

}

?>