<?php

namespace PWE\Modules\FileDownloads;

use FilesystemIterator;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Utils\FilesystemHelper;

class FileDownloads extends PWEModule implements Outputable
{

    private $dl_base;
    private $link_base;

    public function __construct(PWECore $core)
    {
        parent::__construct($core);
        $node = $core->getNode();
        $this->dl_base = $node['!a']['files_base'];
        if (!$this->dl_base) {
            throw new HTTP5xxException("Not configured files base");
        }

        $this->link_base = $node['!a']['download_link'];
    }

    public static function filter_out_cnt($current)
    {
        $ext = strtolower(pathinfo($current, PATHINFO_EXTENSION));

        return $ext !== 'cnt';
    }

    public function process()
    {
        $params = $this->PWE->getURL()->getParamsAsArray();
        $file_path = '/' . $this->dl_base . '/' . FilesystemHelper::protectAgainsRelativePaths(implode('/', $params));
        $file = $this->PWE->getRootDirectory() . $file_path;
        if (!is_file($file)) {
            PWELogger::error("File not found: %s, referer: %s", $file_path, $_SERVER['HTTP_REFERER']);
            throw new HTTP4xxException("File not found", HTTP4xxException::NOT_FOUND);
        }

        $this->recordDownload($file);
        throw new HTTP3xxException($file_path);
    }

    private function recordDownload($file)
    {
        $cnt = $this->getDownloadCount($file);

        $f = $file . '.cnt';
        if (!file_exists($f) || is_writeable($f)) {
            file_put_contents($f, $cnt + 1);
        } else {
            PWELogger::warn("Cannot write download count to %s", $f);
        }
    }

    private function getDownloadCount($file)
    {
        $f = $file . '.cnt';
        if (is_file($f)) {
            $cnt = round(file_get_contents($f));
        } else {
            PWELogger::debug("No download count info for %s", $file);
            $cnt = 0;
        }

        return $cnt;
    }

    public function getDirectoryBlock($subdir)
    {
        $fi = new FilesystemIterator($this->getRealFile($subdir));
        $res = array();
        /** @var $file \SplFileInfo */
        foreach ($fi as $file) {
            if (!self::filter_out_cnt($file)) {
                PWELogger::debug("Filtered out %s", $file);
                continue;
            }
            $res[$file->getMTime() . " " . $file->getBasename()] = $this->getFileBlock($subdir . '/' . $file->getBasename(), '') . "\n\n";
        }

        krsort($res);

        return implode("\n", $res);
    }

    private function getRealFile($file)
    {
        return $this->PWE->getRootDirectory() . '/' . $this->dl_base . '/' . FilesystemHelper::protectAgainsRelativePaths($file);
    }

    public function getFileBlock($orig_file, $comment)
    {
        $orig_file = FilesystemHelper::protectAgainsRelativePaths($orig_file);
        $basename = basename($orig_file);
        $file = $this->getRealFile($orig_file);
        if (!is_file($file)) {
            PWELogger::warn("Broken download: %s", $file);

            return '[broken download: ' . $basename . ']';
        }

        $size = FilesystemHelper::fsys_kbytes(filesize($file));
        $date = date('M d, Y', filemtime($file));
        $link = $this->link_base . '/' . $orig_file;

        $res = "<span class='file_download'>";
        $res .= "<a href='$link'><b>$basename</b></a>";
        $res .= ", <span class='filesize'>$size</span>";
        $res .= ", <span class='filedate'>$date</span>";

        $cnt = $this->getDownloadCount($file);
        if ($cnt) {
            $res .= ", <span class='count'>Download count: $cnt</span>";
        }
        $res .= "<br/><i>$comment</i></span>";

        return $res;
    }

}
