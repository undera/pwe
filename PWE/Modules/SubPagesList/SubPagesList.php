<?php

namespace PWE\Modules\SubPagesList;

use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;

class SubPagesList extends PWEModule implements Outputable
{
    const DEFAULT_COLUMNS = 3;

    public function process()
    {
        $smarty = $this->PWE->getSmarty();
        $node = $this->PWE->getNode();
        $subpages = $node['!c']['url'];

        if (!$this->getPWE()->getURL()->isForcedTrailingSlash()) {
            foreach ($subpages as $n => $page) {
                $page['!a']['link'] = implode('/', $this->getPWE()->getURL()->getMatchedAsArray()) . '/' . $page['!a']['link'];
                $subpages[$n] = $page;
            }
        }
        $smarty->assign('subpages', $subpages);
        $smarty->assign('format', $node['!a']['format']);
        $smarty->assign('columns', $node['!a']['columns'] ? $node['!a']['columns'] : self::DEFAULT_COLUMNS);
        $smarty->setTemplateFile(__DIR__ . "/SubPagesList.tpl");
        $this->PWE->addContent($smarty);
    }

}
