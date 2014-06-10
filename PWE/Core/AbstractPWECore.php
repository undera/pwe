<?php

namespace PWE\Core;

/**
 * FIXME: this superclass have no clue...
 */
abstract class AbstractPWECore {

    private $rootFolder;
    private $dataFolder;
    private $xmlFolder;
    private $tempFolder;
    private $displayTemplate;
    private $staticFolder;
    private $staticHref;

    public function __construct() {
        $this->setRootDirectory(PWEURL::protectAgainsRelativePaths(__DIR__ . '/../../'));
    }

    public function setRootDirectory($dir) {
        PWELogger::debug("Setting PWE root to %s", $dir);
        $this->rootFolder = $dir;
        $this->setDataDirectory($this->rootFolder . '/dat');
        $this->setStaticDirectory($this->rootFolder . '/img');
        $this->setStaticHref('/img');
    }

    public function setDataDirectory($dir) {
        PWELogger::debug("Setting data path to %s", $dir);
        $this->dataFolder = $dir;
        $this->setTempDirectory($this->dataFolder . '/tmp');
        $this->setXMLDirectory($this->dataFolder . '/xml');
    }

    public function setXMLDirectory($dir) {
        PWELogger::debug("Setting XML data path to %s", $dir);
        $this->xmlFolder = $dir;
    }

    public function setTempDirectory($dir) {
        PWELogger::debug("Setting tmp path to %s", $dir);
        $this->tempFolder = $dir;
    }

    public function getRootDirectory() {
        return $this->rootFolder;
    }

    public function getDataDirectory() {
        return $this->dataFolder;
    }

    public function getXMLDirectory() {
        return $this->xmlFolder;
    }

    public function getTempDirectory() {
        return $this->tempFolder;
    }

    public function setDisplayTemplate($tpl) {
        if (!$tpl) {
            PWELogger::debug("No template passed, empty will be used");
            $tpl = self::getEmptyTemplate();
        }

        $this->displayTemplate = $tpl;
    }

    public function getDisplayTemplate() {
        return $this->displayTemplate;
    }

    public static function getEmptyTemplate() {
        return 'empty.tpl';
    }

    public function getStaticDirectory() {
        return $this->staticFolder;
    }

    public function getStaticHref() {
        return $this->staticHref;
    }

    public function setStaticDirectory($dir) {
        $this->staticFolder = $dir;
    }

    public function setStaticHref($href) {
        $this->staticHref = $href;
    }

}

?>