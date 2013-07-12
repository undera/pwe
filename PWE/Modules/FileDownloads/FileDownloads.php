<?php

namespace PWE\Modules\FileDownloads;

use PWE\Core\PWECore;
use PWE\Core\PWEURL;
use PWE\Exceptions\HTTP5xxException;
use PWE\Modules\PWEModule;
use PWE\Utils\FilesystemHelper;

class FileDownloads extends PWEModule {

    private $dl_base;

    public function __construct(PWECore $core) {
        parent::__construct($core);
        $node = $core->getNode();
        $this->dl_base = $node['!a']['downloads_base'];
        if (!$this->dl_base) {
            throw new HTTP5xxException("Not configured downloads base");
        }
    }

    public function getFileBlock($file, $comment) {
        $basename = basename($file);
        $file = $this->PWE->getRootDirectory() . '/' . $this->dl_base . '/' . PWEURL::protectAgainsRelativePaths($file);
        if (!is_file($file)) {
            return '[broken download: ' . $basename . ']';
        }

        $size = FilesystemHelper::fsys_kbytes(filesize($file));
        $date = date('M d, Y', filectime($file));
        $link = $file;

        $res = "<span class='file_download'>";
        $res.="<a href='$link'><b>$basename</b></a>, <span class='filesize'>$size</span>, <span class='filedate'>$date</span>";
        $res.="<br/><i>$comment</i></span>";
        return $res;
    }

}

?>