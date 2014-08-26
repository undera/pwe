<?php /*
  Project: PWE 1.2
  Module: XML functionality
  File: класс чтения/записи XML файлов в иерархический массив
  Author: $Author: Andrey Pohilko$
  Revision: $Revision: 9$
  Note:
 */
?><?php

namespace PWE\Utils;

use Exception;
use InvalidArgumentException;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\PHPFatalException;
use PWE\Modules\Setupable;
use RuntimeException;

class PWEXML extends PWEXMLFunctions implements Setupable
{

    var $cache_dir;
    var $parsed_vals;
    var $parsed_size;
    var $use_cache = true;

    function __construct($cacheFolder = false)
    {
        if (!$cacheFolder)
            $this->use_cache = false;
        else {
            PWELogger::debug("Using xml cache dir: %s", $cacheFolder);
            $this->cache_dir = $cacheFolder;
            $this->use_cache = true;
        }
    }

    ////////////////////////////////////////////////////////
    #                                                      #
    # Перегонка XML-файла в иерархический ассоциативный    #
    # массив                                               #
    #                                                      #
    # Вход:  путь к XML-файлу, переменная, куда записать   #
    # результат                                            #
    # Выход: успешно/неуспешно                             #
    #                                                      #
    ////////////////////////////////////////////////////////
    public function FileToArray($xmlFilename, &$arr)
    {
        if (!file_exists($xmlFilename)) {
            // невозможно найти файл
            throw new RuntimeException("XML file not found: $xmlFilename");
        }

        // read xml contents
        $f = fopen($xmlFilename, 'r');
        flock($f, LOCK_SH);
        $xml_data = file_get_contents($xmlFilename);
        flock($f, LOCK_UN);
        fclose($f);

        if ($this->use_cache) {
            // 2. get md5 from XML
            $md5_xml = md5($xml_data);

            // use cache
            $cache_file = $this->cache_dir . '/xml_' . md5(realpath($xmlFilename)) . '.' . posix_geteuid() . '.php';
            $md5_cache = '';

            // 1. require cache
            if (file_exists($cache_file)) {
                require $cache_file;

                // 3. compare md5's
                // 4. decide where to get array
                if ($md5_xml == $md5_cache) {
                    // cache hit!
                    PWELogger::debug("XML cache hit %s => %s", $xmlFilename, $cache_file);
                    $empty = array();
                    $this->parent_links($arr, $empty);
                    return true;
                }
            } else {
                PWELogger::debug("Cache not found %s for %s", $cache_file, $xmlFilename);
            }
        }

        PWELogger::debug("Parsing XML: %s", $xmlFilename);
        // go parse
        $Parser = xml_parser_create(); // ссылка на новый парсер
        // далее зададим опции для парсинга XML
        xml_parser_set_option($Parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($Parser, XML_OPTION_SKIP_WHITE, 1);


        if (!xml_parse_into_struct($Parser, $xml_data, $this->parsed_vals)) {
            $error = xml_error_string(xml_get_error_code($Parser)) . ' while parsing ' . $xmlFilename;
            xml_parser_free($Parser);
            throw new RuntimeException($error);
        }

        xml_parser_free($Parser);

        $i = 0;
        $arr = array();
        $this->parsed_size = sizeof($this->parsed_vals); // раньше стояло в цикле, но мы дико прокигрывали на этом в производительности
        $this->GetChildren($arr, $i, 1); //обратимся к Дзену!
        // build cache
        if ($this->use_cache) {
            PWELogger::debug("Generating XML cache: %s", $cache_file);
            try {
                $this->ArrayToPHP($arr, "\t", $cache_file, $md5_xml, array(), $xmlFilename);
            } catch (PHPFatalException $e) {
                PWELogger::error("Cannot save parse cache %s", $e);
            }
        }

        return true;
    }

    ////////////////////////////////////////////////////////
    #                                                      #
    # Мааааленькая (как Блондинка) функция записи в ФАЙЛ   #
    #                                                      #
    # Вход:  тот самый массив                              #
    # Выход: XML-файл на диске                             #
    #                                                      #
    ////////////////////////////////////////////////////////
    public function ArrayToFile(array &$a, $fname, $comment = false)
    {
        PWELogger::debug("Saving XML file: %s", $fname);
        if (!is_dir(dirname($fname))) {
            throw new InvalidArgumentException("Directory for saving not exists: " . dirname($fname));
        }

        $bak_dir = $this->use_cache ? $this->cache_dir : dirname($fname);
        $bak_file = $bak_dir . '/' . basename($fname) . '.' . posix_geteuid() . ".bak";
        if (is_file($bak_file)) {
            try {
                unlink($bak_file);
            } catch (PHPFatalException $e) {
                PWELogger::warn('Cannot remove old backup file: %s', $bak_file);
            }
        }

        if (is_file($fname)) {
            try {
                copy($fname, $bak_file);
            } catch (PHPFatalException $e) {
                PWELogger::warn("Unable to create backup file: %s", $bak_file);
            }
        }

        try {
            // записываем результат
            $f = fopen($fname, 'w+');
            if ($f && flock($f, LOCK_EX)) { // открыли и заблокировались?
                fputs($f, "<?xml version='1.0' encoding='UTF-8'?>");
                if ($comment)
                    fputs($f, "\n\n<!-- $comment -->\n");
                $this->generateNode($a, 0, $f); //обратимся к Дзену!
                fflush($f);
                flock($f, LOCK_UN);
                fclose($f);
            } else {
                throw new Exception("Unable to create new result file $fname");
            }

            PWELogger::debug("Checking written file results");
            $tmp_arr = array();
            $this->FileToArray($fname, $tmp_arr);
        } catch (Exception $e) {
            PWELogger::error("Resulting xml file is broken: %s", $fname, $e);
            if (is_file($bak_file)) {
                copy($bak_file, $fname);
            } else {
                if (is_file($fname)) {
                    unlink($fname);
                }
            }
            throw $e;
        }
        return true;
    }

    //////////////////////////////////////////////////////.//
    #                                                       #
    # Перегонка массива иерарохуического назад в XML-строку #
    # Примечание: первая срока с версией не включена        #
    #                                                       #
    # Вход:  тот самый массив                               #
    # Выход: строка XML                                     #
    #                                                       #
    /////////////////////////////////////////////////////////
    private function generateNode(array &$struct, $level, &$fp)
    {
        // оптимизировал все вусмерть
        $tab = str_repeat(' ', $level);
        $nn = "\n";
        $qq = chr(34);
        $l = $nn;

        foreach ($struct ? $struct : array() as $ek => $ev) {
            if (!$ev)
                continue;
            foreach ($ev as $n => $t) {
                $l .= $tab . '<' . $ek;

                foreach ($t['!a'] ? $t['!a'] : array() as $k => $v) {
                    //$v=str_replace('&','&amp;',$v);
                    if ($v == '') {
                        continue; // maybe it's slows us
                    }

                    $l .= ' ' . $k . '=' . $qq . htmlspecialchars($v) . $qq;
                }

                if (!isset($t['!c']) && (!isset($t['!v']) || !strlen(trim($t['!v'])))) //no value  - <tag />
                    $l .= ' />' . $nn;
                else { // container
                    $tmp = '';
                    if (isset($t['!v'])) {
                        if (strpos($t['!v'], '<') !== false) {
                            $t['!v'] = '<![CDATA[' . $t['!v'] . ']]>';
                        }
                        $tmp .= /* $nn.' '. */
                            $tab . str_replace($nn, $nn . ' ' . $tab, $t['!v']);
                    }

                    if (isset($t['!c'])) {
                        $l .= '>' . $tmp;
                        fputs($fp, $l);
                        $this->generateNode($t['!c'], $level + 1, $fp);
                        $l = $tab;
                    } else
                        if (!strstr(trim($tmp), $nn)) // однострочные
                            $l = rtrim($l) . '>' . trim($tmp);
                        else // многострочные
                            $l .= '>' . $nn . $tmp . $nn . $tab;
                    $l .= '</' . $ek . '>' . $nn;
                }
            }
        }

        fputs($fp, $l);
    }

    ////////////////////////////////////////////////////////
    #                                                      #
    # Рекурсивная функция перегонки простого массива       #
    # в иерарохуический                                    #
    #                                                      #
    # Вход:  простой массив,                               #
    # текущий уровень, текущий элемент - оба для рекурсии  #
    # Выход: кусок иерархического массива                  #
    #                                                      #
    ////////////////////////////////////////////////////////
    private function GetChildren(array &$res, &$i, $level)
    {
        // 09.10.2005 17:57:22 целый день потратил на оптимизацию
        // но не зря - есть надежный прирост в производительности
        // все пришлось переделать в рекурсию памяти Асии Асхатовны Валеевой
        // в конце концов без Дзена не обошлось на выход в астрал ушел целый день
        $parent = & $res;
        if ($level > 1)
            $res = & $res['!c'];
        while ($i < $this->parsed_size) {
            $node = & $this->parsed_vals[$i++];
            // нет смысла идти дальше - это другой уровень пошел
            if ($node['level'] !== $level)
                break;

            $res[$node['tag']][] = array();
            $childno = sizeof($res[$node['tag']]) - 1;
            $child = & $res[$node['tag']][$childno];

            $child['!a'] = isset($node['attributes']) ? $node['attributes'] : array();
            $child['!v'] = isset($node['value']) ? trim($node['value']) : '';

            if ($level > 1)
                $child['!p'] = & $parent;

            switch ($node['type']) {
                case 'open':
                    $this->GetChildren($child, $i, $level + 1);
                    break;
            }
        }
    }

    /**
     * рекурсивная функция формирования кэш-файла
     * для иерархического массива, ускоряющего парсинг
     * @param comment комментарий, который будет добавлен в начало файла кэша
     * @param md5_xml контрольная сумма исходного ХМЛ-файла
     */
    private function ArrayToPHP(
        array &$harray, $index, $filename = false, $md5_xml = false, $path = false, $comment = false)
    {
        $result = '';
        // цикл по типам узлов
        foreach ($harray as $ek => $ev) {
            $result .= $index . "'$ek'=>array(\n";
            // цикл по узлам
            foreach ($ev as $nk => $nv) {
                $result .= $index . "\t$nk=>array(\n";

                // attributes
                $result .= $index . "\t\t'!a'=>array(";
                foreach ($nv['!a'] as $ak => $av)
                    $result .= "'$ak'=>\"" . $this->escapeCacheValue($av) . "\", ";
                $result .= "),\n";

                // value
                $result .= $index . "\t\t'!v'=>\"" . $this->escapeCacheValue($nv['!v']) . '",' . "\n";

                // parent
                if (sizeof($path)) {
                    $result .= $index . "\t\t'!p'=>&\$arr";
                    foreach ($path as $k => $v) {
                        $result .= "['$k'][$v]";
                    }
                    $result .= ",\n";
                }

                // children
                if (isset($nv['!c'])) {
                    $newpath = $path;
                    $newpath[$ek] = $nk;
                    $result .= $index . "\t'!c'=>array(\n" . $this->ArrayToPHP($nv['!c'], $index . "\t\t", false, false, $newpath) . "),";
                }
                $result .= $index . "\t),\n";
            }
            $result .= $index . "),\n";
        }

        // сохраняем файлик
        if ($filename) {
            $f = fopen($filename, 'w+');
            if ($f) {
                $res = "<?php // $comment\n";
                $res .= "\$arr=array();\n";
                $res .= "\$arr=array(\n$result);\n\n";
                $res .= '$md5_cache="' . $md5_xml . '"; // checksum' . "\n" . '?' . '>';
                fputs($f, $res);
                fclose($f);
            } else {
                PWELogger::warn('Unable to save file: %s', $filename);
            }
            return true;
        }
        // возврат из рекурсивных функций
        return $result;
    }

    private function escapeCacheValue($value)
    {
        return addcslashes($value, '"$\\');
    }

    private function parent_links(array &$harray, array &$parent)
    {
        // цикл по типам узлов
        foreach ($harray as $ek => $ev) {
            foreach ($ev as $nk => $nv) {
                // parent
                if ($parent)
                    $harray[$ek][$nk]['!p'] = & $parent;

                // children
                if (isset($nv['!c']))
                    $this->parent_links($harray[$ek][$nk]['!c'], $harray[$ek][$nk]);
            }
        }
    }

    public static function setup(PWECore $pwe, array &$registerData)
    {
        if (!$registerData['!c']['cacheDir'])
            $registerData['!c']['cacheDir'][0]['!v'] = $pwe->getTempDirectory();
    }

}

// CLASS болду
?>