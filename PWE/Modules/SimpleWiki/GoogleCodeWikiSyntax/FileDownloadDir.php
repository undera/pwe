<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use PWE\Core\PWECore;
use PWE\Modules\FileDownloads\FileDownloads;
use PWE\Modules\PWEConnected;
use SebastianBergmann\GlobalState\RuntimeException;
use WikiRenderer\TagXhtml;

class FileDownloadDir extends TagXhtml
{

    protected $name = 'file';
    public $beginTag = '<downloads-dir:';
    public $endTag = '>';
    public $separators = array(';');

    public function getContent()
    {
        if ($this->config instanceof PWEConnected) {
            /* @var $pwe PWECore */
            $pwe = $this->config->getPWE();
            $dlCtrl = new FileDownloads($pwe);
            return $dlCtrl->getDirectoryBlock($this->wikiContentArr[0]);
        } else {
            throw new RuntimeException("Not applicable");
        }
    }

    public function isOtherTagAllowed()
    {
        return false;
    }

}

