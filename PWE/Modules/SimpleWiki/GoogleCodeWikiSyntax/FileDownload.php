<?php

namespace PWE\Modules\SimpleWiki\GoogleCodeWikiSyntax;

use PWE\Core\PWEURL;
use PWE\Modules\FileDownloads\FileDownloads;
use WikiRenderer\TagXhtml;

class FileDownload extends TagXhtml {

    protected $name = 'file';
    public $beginTag = '<download:';
    public $endTag = '>';
    public $separators = array(';');

    public function getContent() {
        /* @var $pwe PWECore */
        $pwe = $this->config->getPWE();
        $dlCtrl = new FileDownloads($pwe);
        return $dlCtrl->getFileBlock($this->wikiContentArr[0], $this->wikiContentArr[1]);
    }

    public function isOtherTagAllowed() {
        return false;
    }

}

