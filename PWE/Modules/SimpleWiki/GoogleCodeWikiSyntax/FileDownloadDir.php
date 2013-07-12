<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use PWE\Modules\FileDownloads\FileDownloads;
use WikiRenderer\TagXhtml;

class FileDownloadDir extends TagXhtml {

    protected $name = 'file';
    public $beginTag = '<downloads-dir:';
    public $endTag = '>';
    public $separators = array(';');

    public function getContent() {
        /* @var $pwe PWECore */
        $pwe = $this->config->getPWE();
        $dlCtrl = new FileDownloads($pwe);
        return $dlCtrl->getDirectoryBlock($this->wikiContentArr[0]);
    }

    public function isOtherTagAllowed() {
        return false;
    }

}

