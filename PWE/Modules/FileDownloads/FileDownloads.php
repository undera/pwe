<?php

namespace PWE\Modules\FileDownloads;

use PWE\Core\PWECore;
use PWE\Core\PWEURL;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Utils\FilesystemHelper;

class FileDownloads extends PWEModule implements Outputable {

    private $dl_base;
    private $link_base;

    public function __construct(PWECore $core) {
        parent::__construct($core);
        $node = $core->getNode();
        $this->dl_base = $node['!a']['files_base'];
        if (!$this->dl_base) {
            throw new HTTP5xxException("Not configured files base");
        }

        $this->link_base = $node['!a']['download_link'];
    }

    public function getFileBlock($file, $comment) {
        $basename = basename($file);
        $file = $this->getRealFile($file);
        if (!is_file($file)) {
            \PWE\Core\PWELogger::warn("Broken download: $file");
            return '[broken download: ' . $basename . ']';
        }

        $size = FilesystemHelper::fsys_kbytes(filesize($file));
        $date = date('M d, Y', filemtime($file));
        $link = $this->link_base . '/' . $basename;

        $res = "<span class='file_download'>";
        $res.="<a href='$link'><b>$basename</b></a>";
        $res.=", <span class='filesize'>$size</span>, <span class='filedate'>$date</span>";
        $res.="<br/><i>$comment</i></span>";
        return $res;
    }

    public function process() {
        $params = $this->PWE->getURL()->getParamsAsArray();
        $file_path = '/' . $this->dl_base . '/' . PWEURL::protectAgainsRelativePaths($params[0]);
        $file = $this->PWE->getRootDirectory() . $file_path;
        if (!is_file($file)) {
            throw new HTTP4xxException("File not found", HTTP4xxException::NOT_FOUND);
        }

        throw new HTTP3xxException($file_path);
    }

    private function getRealFile($file) {
        return $this->PWE->getRootDirectory() . '/' . $this->dl_base . '/' . PWEURL::protectAgainsRelativePaths($file);
    }

    public function getDirectoryBlock($subdir) {
        $it = new \FilesystemIterator($this->getRealFile($subdir));
        $res = "";
        foreach ($it as $file) {
            $res.=$this->getFileBlock($subdir.'/'.basename($file), '')."\n\n";
        }
        return $res;
    }

}

?>