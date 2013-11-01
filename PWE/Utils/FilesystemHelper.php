<?php /*
  Project: PWE 1.0
  Module: Файловая система
  File: functions
  Author: OMA 07.05.2006 16:05:13
  Note:
  Update: OMA 07.05.2006 13:12:06
  Note: Инвертирование пордяка проверки существования директорий в fsys_mkdir
 */
?><?php

namespace PWE\Utils;

use PWE\Core\PWELogger;

abstract class FilesystemHelper
{

//----------------------------------------------------------------------------------------
// Считывает содержимое директории в массив
// @see:    $path - путь к директории
//          $is_deep - необходимость считывания вглубь
//          $stat - детали файла/директории
//          $filter - функция-фильтр
// @return: ассоциативный массив содержимого директории
    public static function fsys_readdir($path, $is_deep = false, $stat = false, $filter = "", $root = '')
    {
        if (strrchr($path, '/') != '/')
            $path .= '/';
        $files = array();
        if (!is_dir($path))
            return array();
        // читаем директорию
        $d = opendir($path);
        if ($d) {
            while (false !== ($file = readdir($d))) {
                if ($file != '.' && $file != '..') {
                    $ind = $root . $file; // для формирования относительного пути
                    if (is_dir($path . '/' . $file)) {
                        if ((!$filter || $filter($path . $file)))
                            $files[$ind] = true; // название директории заканчивается слешем

                        if ($is_deep) { // рекурсивное чтение, если запрошено
                            $files = array_merge($files, self::fsys_readdir($path . $file, $is_deep, false, $filter, $ind . '/'));
                        }
                    } else
                        if ((!$filter || $filter($path . $file)))
                            $files[$ind] = false;
                }
            }
            closedir($d);
            // мультисортировка 1) по "директория/файл"; 2) название в алфавитном порядке
            array_multisort($files, SORT_DESC, array_keys($files), SORT_STRING);
            // детали эелементов директории
            if ($stat)
                foreach ($files as $f => $file)
                    $files[$f] = array_merge(stat($path . '/' . $f), array('is_dir' => $file));
        }
        return $files;
    }

//----------------------------------------------------------------------------------------
// Копирует/перемещает содержимое директории в другую директорию
// @see:    $from - путь к директории, с которой копируется
//          $to - путь к директории, в которую копируется
//          $is_deep - необходимость копирования содержимого поддиректорий
//          $move - удалить после копирования
//          $is_root - первый заход (private)
// @return: результат копирования/перемещения
    public static function fsys_copydir($from, $to, $is_deep = true, $move = false)
    {
        if (!is_dir($from)) {
            PWELogger::warning("Source directory not exists for copy: $from");
            return false;
        }
        PWELogger::debug("Copying $from to $to");
        // закрываем путь к директории слешем
        if (strrchr($from, '/') != '/')
            $from .= '/';
        if (strrchr($to, '/') != '/')
            $to .= '/';
        // получили массив содержания директории
        $files = self::fsys_readdir($from, $is_deep);
        // конечная директория, должна существовать либо пробуем ее создать
        // при этом если отсутствует более одного уровня директорий, то вернуть ошибку
        if (!is_dir($to)) {
            if (!self::fsys_mkdir($to)) {
                PWELogger::error("Unable to create destination directory: $to");
                return false;
            }

            if (!is_dir(preg_replace('!/\w+/$!', '/', $to))) {
                PWELogger::error("Unable to use destination directory: $to");
                return false;
            }
        }

        // конечная директория должна давать права на запись
        if (!is_writable($to)) {
            PWELogger::error("Destination dir '$to' is not writable");
            return false;
        }

        // проверка на право чтения с директории-источника и на право удаления при перемещении
        foreach ($files as $file => $is_dir) {
            if (!is_readable($from . $file)) {
                PWELogger::error("Source '$from.$file' is not readable");
                return false;
            }

            if ($move && !is_writable($from . $file)) {
                PWELogger::error("Source '$from.$file' is not writable for move");
                return false;
            }
        }

        // существующие файлы и директории в конечной директории, для правильного отката
        $existed_files = self::fsys_readdir($to);
        $copied = true; // метка - все ли было скопировано - для отката

        foreach ($files as $path => $is_dir) {
            $newpath = $to . $path; // формируем конечный путь
            $oldpath = $from . $path;
            if ($is_dir) {
                if ($is_deep && !is_dir($newpath)) {
                    $copied = self::fsys_mkdir($newpath);
                    if (!$copied)
                        break;
                }
            } else {
                $copied = copy($oldpath, $newpath);
                PWELogger::debug("Copy file " . ($copied ? 'success' : 'FAILED') . ": $oldpath -> $newpath");
                // если копируется пустой файл, то copy() возвращает false (?)
                if (!$copied && (!file_exists($newpath) || filesize($oldpath) > 0))
                    break;
            }
        }

        if (!$copied) { //  откат
            PWELogger::info("Rolling back...");
            self::fsys_removedir($to, $is_deep, $existed_files);
            return false;
        }
        // удалить источник при перемещении
        if ($move && !self::fsys_removedir($from, $is_deep))
            return false;
        return true;
    }

//----------------------------------------------------------------------------------------
// Перемещает содержимое директории в другую директорию
// @see:    $from - путь к директории, с которой копируется
//          $to - путь к директории, в которую копируется
//          $is_deep - необходимость копирования содержимого поддиректорий
// @return: результат перемещения
// @note:   может не нужен
    public static function fsys_movedir($from, $to, $is_deep = true)
    {
        return self::fsys_copydir($from, $to, $is_deep, true);
    }

//----------------------------------------------------------------------------------------
// Удаляет содержимое директории
// @see:    $dir - путь к директории
//          $is_deep - необходимость удаления содержимого поддиректорий
//          $leave_files - массив-список неудаляемых файлов/директорий
// @return: результат удаления
    function fsys_removedir($dir, $is_deep = true, $leave_files = array())
    {
        if (!is_dir($dir))
            return true;
        if (strrchr($dir, '/') != '/')
            $dir .= '/';
        // получили массив содержания директории
        $files = self::fsys_readdir($dir, $is_deep);

        // проверка на удаляемость эелементов
        foreach ($files as $file => $is_dir) {
            if ($is_dir && $is_deep && !is_writable($dir . $file))
                return false;
            elseif (!$is_dir && !is_writable($dir . $file))
                return false;
        }

        foreach ($files as $path => $is_dir) {
            // пропускать указанные "неудаляемые" элементы
            if (in_array($path, $leave_files))
                continue;

            if ($is_dir) {
                if ($is_deep) {

                    if (!self::fsys_removedir($dir . $path, $is_deep, $leave_files)) {
                        PWELogger::debug('Cannot delete dir: ' . $dir . $path);
                        return false;
                    }
                }
            } else {
                if (file_exists($dir . $path))
                    if (!unlink($dir . $path)) {
                        PWELogger::debug('Cannot delete: ' . $dir . $path);
                        return false;
                    }
            }
        }
        // попытка удаления верхней директории
        if (!sizeof($leave_files) && !rmdir($dir))
            return false;

        return true;
    }

//----------------------------------------------------------------------------------------
// Создает директорию
// @see:    $dir - полный путь к директории
// @return: результат создания
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
                    PWELogger::debug("Creating directory: $cdir");
                    if (!mkdir($cdir)) { // если создать директорию не вышло, то откат
                        if ($notexisted)
                            self::fsys_removedir($notexisted);
                        PWELogger::debug('Failed to create directory ' . $cdir);
                        return false;
                    }
                    if (!$notexisted)
                        $notexisted = $cdir;
                }
                if (!is_writable($cdir)) { // если в директорию нельзя писать, то откат
                    if ($notexisted)
                        self::fsys_removedir($notexisted);
                    PWELogger::debug('Directory not writable ' . $cdir);
                    return false;
                }
            }
        }

        return true;
    }

//----------------------------------------------------------------------------------------
// Умный размер файла/директории
// @see:    $file - полный путь к файлу
// @return: размер файла
    public static function fsys_filesize($file)
    {
        if (!file_exists($file))
            return false;
        if (is_dir($file)) { // директория
            if (strrchr($file, '/') != '/')
                $file .= '/';
            $files = self::fsys_readdir($file, true);
            $files = array_reverse($files); // перевернем массив, т.к. файлы снизу
            $dsize = 0;
            foreach ($files as $path => $is_dir) {
                if ($is_dir)
                    break;
                //// встретили первую директорию, уходим из цикла
                $dsize += filesize($file . $path);
            }
            return $dsize;
        } else {
            return filesize($file);
        }
    }

//----------------------------------------------------------------------------------------
// Преобразование любого числа в умный размер файла
// @see:    $size - число - кол-во байтов
// @return: умный размер
    public static function fsys_kbytes($size)
    {
        $slevel = array('B', 'KB', 'MB', 'GB', 'TB'); // допустимые метки размеров
        $lvl = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $lvl++;
            if ($lvl == 4)
                break;
        }
        $size = round($size, 2); // округление до сотых
        if (intval($size) == $size)
            return intval($size) . ' ' . $slevel[$lvl];
        else
            return $size . ' ' . $slevel[$lvl];
    }

}

?>