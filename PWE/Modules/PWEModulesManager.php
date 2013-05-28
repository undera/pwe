<?php

namespace PWE\Modules;

use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Utils\PWEXML;
use \RuntimeException;
use \ReflectionClass;
use \InvalidArgumentException;
use PWE\Exceptions\PHPFatalException;

class PWEModulesManager {

    protected $registryFile;
    protected $registryArray;

    /**
     *
     * @var PWECore
     */
    private $PWE;

    public function __construct(PWECore $pwe, $registryFile = NULL) {
        PWELogger::debug("Loading registry");
        $this->PWE = $pwe;
        if (!$registryFile) {
            $registryFile = $this->PWE->getXMLDirectory() . '/eg_globals.xml';
        }
        $this->setRegistryFile($registryFile);
    }

    public function setRegistryFile($path) {
        PWELogger::debug("Setting registry file to: " . $path);
        $this->registryFile = $path;
        $this->loadRegistry();
    }

    protected function &getModuleNode($name) {
        $node = &$this->registryArray['registry'][0]['!c']['classPaths'][0];
        $path = explode("\\", $name);
        foreach ($path as $component) {
            if (!$component)
                continue;

            if (!$node['!c'][$component][0]) {
                $node['!c'][$component][0] = array();
            }

            $node = &$node['!c'][$component][0];
        }

        //print_r($this->registryArray);
        return $node;
    }

    public function registerModule($name) {
        PWELogger::debug("Registering module $name");
        $mod = &$this->getModuleNode($name);

        try {
            $modClass = new ReflectionClass($name);

            if ($modClass->isInstantiable()) {
                if ($modClass->implementsInterface('PWE\Modules\Setupable')) {
                    PWELogger::debug("Setting up $name");
                    $name::setup($this->PWE, $mod);
                }
            }

            $this->saveRegistry();
        } catch (\ReflectionException $e) {
            PWELogger::error("Failed to register module: $name", $e);
        }
    }

    // FIXME: get settings not consistent with set settings
    public function &getModuleSettings($name) {
        $mod = &$this->getModuleNode($name);
        return $mod;
    }

    /**
     *
     * @param string $moduleName
     * @return PWEModule
     */
    public function getSingleInstanceModule($moduleName) {
        PWELogger::debug("Module class: " . $moduleName);
        $module = new $moduleName($this->PWE);
        return $module;
    }

    /**
     *
     * @param array $structureNode
     * @return PWEModule
     */
    public function getMultiInstanceModule(array $structureNode) {
        if (!isset($structureNode['!a']['class'])) {
            //PWELogger::debug("Node: ", $structureNode);
            throw new InvalidArgumentException("Passed structure node have no class name");
        }

        $mod = $this->getSingleInstanceModule($structureNode['!a']['class']);
        return $mod;
    }

    protected function saveRegistry() {
        try {
            PWEXML::cleanEmptyNodes($this->registryArray['registry'][0]);
        } catch (PHPFatalException $e) {
            PWELogger::warn("Failed cleaning empty nodes: ", $e);
        }
        PWELogger::warn("Saving registry file: " . $this->registryFile);
        $XML = new PWEXML($this->PWE->getTempDirectory());
        $XML->ArrayToFile($this->registryArray, $this->registryFile);
        $XML->FileToArray($this->registryFile, $this->registryArray);
    }

    protected function loadRegistry() {
        // read site structure
        $XML = new PWEXML($this->PWE->getTempDirectory());
        $this->registryArray = array();
        try {
            $XML->FileToArray($this->registryFile, $this->registryArray);

            $logger = $this->getModuleSettings('PWE\Core\PWELogger');
            if ($logger['!a']) {
                PWELogger::setLevel(PWELogger::getLevelByName($logger['!a']['level']));
            }
        } catch (RuntimeException $e) {
            PWELogger::warning("Cannot load registry file: " . $e->getMessage());
        }
    }

}

?>