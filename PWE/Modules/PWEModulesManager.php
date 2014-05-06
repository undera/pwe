<?php

namespace PWE\Modules;

use InvalidArgumentException;
use PWE\Core\PWECMDJob;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\PHPFatalException;
use PWE\Utils\PWEXML;
use ReflectionClass;
use RuntimeException;

class PWEModulesManager implements PWECMDJob
{

    protected $registryFile;
    protected $registryArray;

    /**
     *
     * @var PWECore
     */
    private $PWE;

    public function __construct(PWECore $pwe)
    {
        PWELogger::debug("Loading registry");
        $this->PWE = $pwe;
        $registryFile = $this->PWE->getXMLDirectory() . '/eg_globals.xml';
        $this->setRegistryFile($registryFile);
    }

    public function getRegistryFile()
    {
        return $this->registryFile;
    }

    public function setRegistryFile($path)
    {
        PWELogger::debug("Setting registry file to: %s", $path);
        $this->registryFile = $path;
        $this->loadRegistry();
    }

    protected function &getModuleNode($name)
    {
        $node = & $this->registryArray['registry'][0]['!c']['classPaths'][0];
        $path = explode("\\", $name);
        foreach ($path as $component) {
            if (!$component)
                continue;

            if (!$node['!c'][$component][0]) {
                $node['!c'][$component][0] = array();
            }

            $node = & $node['!c'][$component][0];
        }

        //print_r($this->registryArray);
        return $node;
    }

    public function registerModule($name)
    {
        PWELogger::debug("Registering module %s", $name);
        $mod = & $this->getModuleNode($name);

        try {
            $modClass = new ReflectionClass($name);

            if ($modClass->isInstantiable()) {
                if ($modClass->implementsInterface('PWE\Modules\Setupable')) {
                    PWELogger::debug("Setting up %s", $name);
                    $name::setup($this->PWE, $mod);
                }
            }

            $this->saveRegistry();
        } catch (\ReflectionException $e) {
            PWELogger::error("Failed to register module %s: %s", $name, $e);
        }
    }

    // FIXME: get settings not consistent with set settings
    public function &getModuleSettings($name)
    {
        $mod = & $this->getModuleNode($name);
        return $mod;
    }

    /**
     *
     * @param string $moduleName
     * @return PWEModule
     */
    public function getSingleInstanceModule($moduleName)
    {
        PWELogger::debug("Module class: %s", $moduleName);
        $module = new $moduleName($this->PWE);
        return $module;
    }

    /**
     *
     * @param array $structureNode
     * @throws \InvalidArgumentException
     * @return PWEModule
     */
    public function getMultiInstanceModule(array $structureNode)
    {
        if (!isset($structureNode['!a']['class'])) {
            //PWELogger::debug("Node: ", $structureNode);
            throw new InvalidArgumentException("Passed structure node have no class name");
        }

        $mod = $this->getSingleInstanceModule($structureNode['!a']['class']);
        return $mod;
    }

    protected function saveRegistry()
    {
        try {
            PWEXML::cleanEmptyNodes($this->registryArray['registry'][0]);
        } catch (PHPFatalException $e) {
            PWELogger::warn("Failed cleaning empty nodes: %s", $e);
        }
        PWELogger::warn("Saving registry file: %s", $this->registryFile);
        $XML = new PWEXML($this->PWE->getTempDirectory());
        $XML->ArrayToFile($this->registryArray, $this->registryFile);
        $XML->FileToArray($this->registryFile, $this->registryArray);
    }

    protected function loadRegistry()
    {
        PWELogger::debug("Loading registry file: %s", $this->registryFile);
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
            PWELogger::warn("Cannot load registry file: %s", $e->getMessage());
        }
    }

    public function run()
    {
        $this->setRegistryFile($this->PWE->getModulesManager()->getRegistryFile());
        PWELogger::debug("Dumping config");
        $this->xml_as_options($this->registryArray);
    }

    private function xml_as_options(&$arr, $stack = array())
    {
        foreach ($arr ? $arr : array() as $name => $nodes) {
            array_push($stack, $name);
            foreach ($nodes ? $nodes : array() as $i => $node) {
                if (sizeof($nodes) > 1) {
                    array_push($stack, $i);
                }

                if (strlen($node['!v'])) {
                    echo implode('.', $stack) . "=" . $node['!v'] . "\n";
                }

                foreach ($node['!a'] ? $node['!a'] : array() as $name => $val) {
                    echo implode('.', $stack) . ".$name=$val\n";
                }
                if (sizeof($nodes) > 1) {
                    array_pop($stack);
                }

                $this->xml_as_options($node['!c'], $stack);
            }
            array_pop($stack);
        }
    }
}

?>