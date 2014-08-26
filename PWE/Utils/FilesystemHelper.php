<?php

namespace PWE\Utils;

use PWE\Core\PWELogger;

abstract class FilesystemHelper
{

    public static function fsys_readdir($path, $is_deep = false, $stat = false, $filter = "", $root = '')
    {
        if (strrchr($path, '/') != '/') {
            $path .= '/';
        }
        $files = array();
        if (!is_dir($path)) {
            return array();
        }

        $d = opendir($path);
        if ($d) {
            while (false !== ($file = readdir($d))) {
                if ($file != '.' && $file != '..') {
                    $ind = $root . $file;
                    if (is_dir($path . '/' . $file)) {
                        if ((!$filter || $filter($path . $file))) {
                            $files[$ind] = true;
                        }

                        if ($is_deep) {
                            $files += self::fsys_readdir($path . $file, $is_deep, false, $filter, $ind . '/');
                        }
                    } else
                        if ((!$filter || $filter($path . $file))) {
                            $files[$ind] = false;
                        }
                }
            }
            closedir($d);

            array_multisort($files, SORT_DESC, array_keys($files), SORT_STRING);

            if ($stat)
                foreach ($files as $f => $file)
                    $files[$f] = stat($path . '/' . $f) + array('is_dir' => $file);
        }
        return $files;
    }

    public static function fsys_copydir($from, $to, $is_deep = true, $move = false)
    {
        if (!is_dir($from)) {
            PWELogger::warn("Source directory not exists for copy: %s", $from);
            return false;
        }
        PWELogger::debug("Copying %s to %s", $from, $to);

        if (strrchr($from, '/') != '/')
            $from .= '/';
        if (strrchr($to, '/') != '/')
            $to .= '/';

        $files = self::fsys_readdir($from, $is_deep);

        if (!is_dir($to)) {
            if (!self::fsys_mkdir($to)) {
                PWELogger::error("Unable to create destination directory: %s", $to);
                return false;
            }

            if (!is_dir(preg_replace('!/\w+/$!', '/', $to))) {
                PWELogger::error("Unable to use destination directory: %s", $to);
                return false;
            }
        }

        if (!is_writable($to)) {
            PWELogger::error("Destination dir '%s' is not writable", $to);
            return false;
        }

        foreach ($files as $file => $is_dir) {
            if (!is_readable($from . $file)) {
                PWELogger::error("Source '%s.%s' is not readable", $from, $file);
                return false;
            }

            if ($move && !is_writable($from . $file)) {
                PWELogger::error("Source '%s.%s' is not writable for move", $from, $file);
                return false;
            }
        }

        $existed_files = self::fsys_readdir($to);
        $copied = true;

        foreach ($files as $path => $is_dir) {
            $newpath = $to . $path;
            $oldpath = $from . $path;
            if ($is_dir) {
                if ($is_deep && !is_dir($newpath)) {
                    $copied = self::fsys_mkdir($newpath);
                    if (!$copied)
                        break;
                }
            } else {
                $copied = copy($oldpath, $newpath);
                PWELogger::debug("Copy file %s %s -> %s", ($copied ? 'success' : 'FAILED'), $oldpath, $newpath);

                if (!$copied && (!file_exists($newpath) || filesize($oldpath) > 0))
                    break;
            }
        }

        if (!$copied) {
            PWELogger::info("Rolling back...");
            self::fsys_removedir($to, $is_deep, $existed_files);
            return false;
        }

        if ($move && !self::fsys_removedir($from, $is_deep))
            return false;
        return true;
    }

    public static function fsys_movedir($from, $to, $is_deep = true)
    {
        return self::fsys_copydir($from, $to, $is_deep, true);
    }

    function fsys_removedir($dir, $is_deep = true, $leave_files = array())
    {
        if (!is_dir($dir))
            return true;
        if (strrchr($dir, '/') != '/')
            $dir .= '/';

        $files = self::fsys_readdir($dir, $is_deep);

        foreach ($files as $file => $is_dir) {
            if ($is_dir && $is_deep && !is_writable($dir . $file))
                return false;
            elseif (!$is_dir && !is_writable($dir . $file))
                return false;
        }

        foreach ($files as $path => $is_dir) {
            if (in_array($path, $leave_files))
                continue;

            if ($is_dir) {
                if ($is_deep) {

                    if (!self::fsys_removedir($dir . $path, $is_deep, $leave_files)) {
                        PWELogger::debug('Cannot delete dir: %s%s', $dir, $path);
                        return false;
                    }
                }
            } else {
                if (file_exists($dir . $path))
                    if (!unlink($dir . $path)) {
                        PWELogger::debug('Cannot delete: %s%s', $dir, $path);
                        return false;
                    }
            }
        }

        if (!sizeof($leave_files) && !rmdir($dir))
            return false;

        return true;
    }

    public static function fsys_mkdir($dir)
    {
        $notexisted = false;
        $memo = false;
        $dir = str_replace('\\', '/', $dir); // корректируем слеши
        $cdir = $dir;
        $cdir = preg_replace('!/$!', '', $cdir);
        while ($cdir != '' && strpos($cdir, '/') !== false) { // проходимся по директоиям, проверяя их существование
            if (!is_dir($cdir))
                $memo[] = $cdir;
            else
                break;

            $cdir = substr($cdir, 0, strrpos($cdir, '/'));
        }

        if ($memo) { // если есть несуществующие директории
            $memo = array_reverse($memo);
            foreach ($memo as $cdir) {
                if (!is_dir($cdir)) { // еще раз проверим, на всякий случай
                    PWELogger::debug("Creating directory: %s", $cdir);
                    if (!mkdir($cdir)) { // если создать директорию не вышло, то откат
                        if ($notexisted)
                            self::fsys_removedir($notexisted);
                        PWELogger::debug('Failed to create directory %s', $cdir);
                        return false;
                    }
                    if (!$notexisted)
                        $notexisted = $cdir;
                }
                if (!is_writable($cdir)) { // если в директорию нельзя писать, то откат
                    if ($notexisted)
                        self::fsys_removedir($notexisted);
                    PWELogger::debug('Directory not writable %s', $cdir);
                    return false;
                }
            }
        }

        return true;
    }

    public static function fsys_filesize($file)
    {
        if (!file_exists($file))
            return false;
        if (is_dir($file)) {
            if (strrchr($file, '/') != '/')
                $file .= '/';
            $files = self::fsys_readdir($file, true);
            $files = array_reverse($files);
            $dsize = 0;
            foreach ($files as $path => $is_dir) {
                if ($is_dir)
                    break;

                $dsize += filesize($file . $path);
            }
            return $dsize;
        } else {
            return filesize($file);
        }
    }


    public static function fsys_kbytes($size)
    {
        $slevel = array('B', 'KB', 'MB', 'GB', 'TB');
        $lvl = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $lvl++;
            if ($lvl == 4)
                break;
        }
        $size = round($size, 2);
        if (intval($size) == $size)
            return intval($size) . ' ' . $slevel[$lvl];
        else
            return $size . ' ' . $slevel[$lvl];
    }

}

?>