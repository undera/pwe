<?php

namespace PWE\Core;

use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Lib\Smarty\SmartyAssociative;

class PWEURL implements SmartyAssociative {

    private $URLArrayMatched = array();

    public function __construct($uri) {
        $this->parseURL($uri);
        $this->detectSubdirectory();
        $this->URLArrayParams = $this->URLArray;
    }

    private function parseURL($uri) {
        if ($uri[0] != '/')
            throw new HTTP4xxException('URL must start with /', HTTP4xxException::BAD_REQUEST);

        $uri = parse_url($uri);
        if (!$uri)
            throw new HTTP4xxException("Requested URI incorrect", HTTP4xxException::BAD_REQUEST);

        $this->URL = urldecode($uri['path']);
        $this->URLArray = explode('/', $this->URL);

        // 1.2. Переадресация некорректных URL
        if (in_array('..', $this->URLArray) || in_array('.', $this->URLArray) || strstr($this->URL, '//')) {
            $goto = str_replace('/../', '/', $this->URL);
            $goto = str_replace('/.', '', $goto);
            $goto = str_replace('//', '/', $goto);
            throw new HTTP3xxException($goto, HTTP3xxException::PERMANENT);
        }
    }

    private function detectSubdirectory() {
        // 2.0 Определение субдиректории запуска
        $docroot = explode(DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']); // это просто для скорости
        $script_dir = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));

        foreach ($docroot as $k => $docroot_item) {
            if ($script_dir[$k] != $docroot_item) {
                PWELogger::warning("SCRIPT_FILENAME points outside of DOCUMENT_ROOT");
                return;
            } else {
                if (strlen($docroot_item)) {
                    unset($script_dir[$k]);
                }
            }
        }

        $this->baseDirectory = implode('/', $script_dir);

        if (strlen($this->baseDirectory)) {
            $this->baseDirectory = str_replace('\\', '/', $this->baseDirectory); // совместимость с дебильными виндовозными обратными слэшами
            // приведем массив в норму
            foreach (explode('/', $this->baseDirectory) as $k => $v)
                if ($k)
                    unset($this->URLArray[$k]);
            $this->URLArray = array_values($this->URLArray); // чиним сбитую нумерацию
        }
    }

    public function getFullAsArray($stripLastEmpty = true) {
        $params = $this->URLArray;
        if ($stripLastEmpty && !strlen(end($params))) {
            array_pop($params);
        }

        return $params;
    }

    /**
     * helper method for smarty
     * @return int
     */
    public function getFullCount() {
        return sizeof($this->URLArray);
    }

    public function getMatchedAsArray($stripLastEmpty = true) {
        $params = $this->URLArrayMatched;
        if ($stripLastEmpty && !strlen(end($params))) {
            array_pop($params);
        }

        return $params;
    }

    public function getMatchedCount() {
        return sizeof($this->URLArrayMatched);
    }

    public function getParamsAsArray($stripLastEmpty = true) {
        $params = $this->URLArrayParams;
        if ($stripLastEmpty && !strlen(end($params))) {
            array_pop($params);
        }

        return $params;
    }

    public function getParamsCount() {
        return sizeof($this->URLArrayParams);
    }

    public function setMatchedDepth($depth) {
        for ($n = 0; $n < $depth; $n++) {
            array_push($this->URLArrayMatched, array_shift($this->URLArrayParams));
        }
        PWELogger::debug("Matched depth $depth, params:", $this->URLArrayParams);
    }

    public static function getSmartyAllowedMethods() {
        return array('getFullCount', 'getMatchedCount', 'getParamsCount',
            'getFullAsArray', 'getMatchedAsArray', 'getParamsAsArray',);
    }

    public static function protectAgainsRelativePaths($path) {
        $sep = '/';
        $absolutes = array();

        $path = str_replace(array('/', '\\'), $sep, $path);
        $exploded = explode($sep, $path);

        if ($exploded[0] == '')
            $absolutes[] = '';
        $parts = array_filter($exploded, 'strlen');

        foreach ($parts as $part) {
            if ('.' == $part)
                continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode($sep, $absolutes);
    }

}

?>