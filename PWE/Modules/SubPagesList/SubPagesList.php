<?php

namespace PWE\Modules\SubPagesList;

use PWE\Modules\PWEModule;
use PWE\Modules\Outputable;

class SubPagesList extends PWEModule implements Outputable {
    const DEFAULT_COLUMNS=3;

    public function process() {
        $smarty = $this->PWE->getSmarty();
        $node = $this->PWE->getNode();
        $smarty->assign('subpages', $node['!c']['url']);
        $smarty->assign('format', $node['!a']['format']);
        $smarty->assign('columns', $node['!a']['columns'] ? $node['!a']['columns'] : self::DEFAULT_COLUMNS);
        $smarty->setTemplateFile(dirname(__FILE__) . "/SubPagesList.tpl");
        $this->PWE->addContent($smarty);
    }

}

?>