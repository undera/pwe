<?php

namespace PWE\Core;

use BadFunctionCallException;
use PWE\Modules\PWEModule;
use PWE\Modules\PWEModulesManager;
use PWE\Utils\FilesystemHelper;

abstract class AbstractPWECore implements PWECore
{
    /**
     *
     * @var PWEModulesManager
     */
    protected $modulesManager;

    private $rootFolder;
    private $dataFolder;
    private $xmlFolder;
    private $tempFolder;

    public function __construct()
    {
        $this->setRootDirectory(FilesystemHelper::protectAgainsRelativePaths(__DIR__ . '/../../'));
    }

    public function setRootDirectory($dir)
    {
        PWELogger::debug("Setting PWE root to %s", $dir);
        $this->rootFolder = $dir;
        $this->setDataDirectory($this->rootFolder . '/dat');
    }

    public function setDataDirectory($dir)
    {
        PWELogger::debug("Setting data path to %s", $dir);
        $this->dataFolder = $dir;
        $this->setTempDirectory($this->dataFolder . '/tmp');
        $this->setXMLDirectory($this->dataFolder . '/xml');
    }

    public function setXMLDirectory($dir)
    {
        PWELogger::debug("Setting XML data path to %s", $dir);
        $this->xmlFolder = $dir;
    }

    public function setTempDirectory($dir)
    {
        PWELogger::debug("Setting tmp path to %s", $dir);
        $this->tempFolder = $dir;
    }

    public function getRootDirectory()
    {
        return $this->rootFolder;
    }

    public function getDataDirectory()
    {
        return $this->dataFolder;
    }

    public function getXMLDirectory()
    {
        return $this->xmlFolder;
    }

    public function getTempDirectory()
    {
        return $this->tempFolder;
    }

    /**
     *
     * @param mixed $structureNode
     * @return PWEModule
     */
    public function getModuleInstance($structureNode)
    {
        if (is_array($structureNode)) {
            return $this->modulesManager->getMultiInstanceModule($structureNode);
        } else {
            return $this->modulesManager->getSingleInstanceModule($structureNode);
        }
    }

    protected function createModulesManager(PWEModulesManager $externalManager = null)
    {
        $this->modulesManager = $externalManager ? $externalManager : new PWEModulesManager($this);
    }

    /**
     * @throws BadFunctionCallException
     * @return PWEModulesManager
     */
    public function getModulesManager()
    {
        if (!$this->modulesManager)
            throw new BadFunctionCallException("Not created modules manager");

        return $this->modulesManager;
    }

}

?>