<?php

namespace PWE\Core;

use BadFunctionCallException;
use PWE\Auth\PWEUserAuthController;
use PWE\Exceptions\HTTP2xxException;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Exceptions\PWEHTTPException;
use PWE\Lib\Smarty\SmartyWrapper;
use PWE\Modules\MenuGenerator;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Modules\PWEModulesManager;
use PWE\Utils\FilesystemHelper;
use PWE\Utils\PWEXML;
use PWE\Utils\PWEXMLFunctions;
use RuntimeException;

class PWECore
{
    /**
     *
     * @var PWEURL
     */
    protected $URL;
    protected $siteStructureFile;
    /**
     *
     * @var PWEModule
     */
    private $currentModuleInstance;
    private $statusSent = false;
    private $siteStructure;
    private $htmlContent = array();
    private $errorsTemplate;
    private $displayTemplate;
    private $staticFolder;
    private $staticHref;

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
        $this->errorsTemplate = 'error.tpl';
    }

    public function setRootDirectory($dir)
    {
        PWELogger::debug("Setting PWE root to %s", $dir);
        $this->rootFolder = $dir;
        $this->setDataDirectory($this->rootFolder . '/dat');
        $this->setStaticDirectory($this->getRootDirectory() . '/img');
        $this->setStaticHref('/img');
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
        $this->siteStructureFile = $this->getXMLDirectory() . '/out.xml';

        if ($this->modulesManager) {
            $this->modulesManager->setRegistryFile($this->getXMLDirectory() . '/eg_globals.xml');
        }
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


    public static function getSmartyAllowedMethods()
    {
        return array('getStructLevel',
            'getContent', 'getNode',
            'getCurrentModuleInstance',
            'getStaticDirectory', 'getStaticHref');
    }

    public static function getEmptyTemplate()
    {
        return 'empty.tpl';
    }

    public function process($uri)
    {
        try {
            $this->createModulesManager();
            $this->setURL($uri);
            $this->currentModuleInstance = $this->getModuleInstance($this->getNode());
            return $this->getHTML();
        } catch (HTTP2xxException $e) {
            PWELogger::debug("Got 2xx exception");
            $this->sendHTTPStatusCode($e->getCode());
            if ($e->isUsingTemplate())
                return $this->getHTML();
            else
                return $e->getMessage();
        } catch (HTTP3xxException $e) {
            PWELogger::debug("Got 3xx exception");
            $this->sendHTTPStatusCode($e->getCode());
            return "";
        }
    }

    /**
     *
     * @return array
     * @throws HTTP5xxException
     */
    public function &getNode()
    {
        if ($this->URL==null) {
            throw new HTTP5xxException("No URL set");
        }
        return $this->URL->getNode();
    }

    /**
     * @throws \PWE\Exceptions\HTTP5xxException
     * @return string
     */
    private function getHTML()
    {
        /** @var $module Outputable */
        $module = $this->getCurrentModuleInstance();
        if (!($module instanceof Outputable)) {
            throw new HTTP5xxException("Module class is not Outputable");
        }
        $module->process();

        $smarty = $this->getSmarty();
        $smarty->setTemplateFile($this->getDisplayTemplate());
        $smarty->assign('node', $this->getNode());
        PWELogger::info("Processing main template: %s", $this->getDisplayTemplate());
        return $smarty->fetchAll();
    }

    public function getCurrentModuleInstance()
    {
        return $this->currentModuleInstance;
    }

    /**
     * @return SmartyWrapper
     */
    public function getSmarty()
    {
        $smarty = new SmartyWrapper($this);
        $smarty->addTemplateDir(__DIR__ . '/../tpl');
        $smarty->addTemplateDir($this->getDataDirectory() . '/tpl');
        $smarty->registerObject('PWE', $this);
        $smarty->registerObject('URL', $this->getURL());
        $smarty->registerObject('AUTH', PWEUserAuthController::getAuthControllerInstance($this));
        return $smarty;
    }

    /**
     *
     * @throws HTTP4xxException
     * @return PWEURL
     */
    public function getURL()
    {
        if (!$this->URL) {
            throw new HTTP4xxException("No URL set", HTTP4xxException::BAD_REQUEST);
        }

        return $this->URL;
    }

    /**
     *
     * @param string $uri
     * @throws \RuntimeException
     */
    protected function setURL($uri)
    {
        if ($this->URL) {
            throw new RuntimeException("setURL must be called only once");
        }

        if (!$this->siteStructure) {
            $this->siteStructure = $this->getSiteStructure();
        }

        PWELogger::info("=== %s %s", $_SERVER['REQUEST_METHOD'], $uri);
        PWELogger::debug("Request variables: %s", $_REQUEST);
        $this->URL = new PWEURL($uri, $this->siteStructure);

        $node = $this->getNode();
        $this->setDisplayTemplate($node['!i']['template']);

        $this->jumpToFirstChild();

        /**
         * @var PWEUserAuthController
         */
        $authController = PWEUserAuthController::getAuthControllerInstance($this);
        if ($authController != null) {
            PWELogger::debug("Starting auth");
            $authController->handleAuth();
        } else {
            PWELogger::warn("No auth controller");
        }
        PWELogger::debug('Done auth');
    }

    public function getDisplayTemplate()
    {
        return $this->displayTemplate;
    }

    public function setDisplayTemplate($tpl)
    {
        if (!$tpl) {
            PWELogger::debug("No template passed, empty will be used");
            $tpl = self::getEmptyTemplate();
        }

        $this->displayTemplate = $tpl;
    }

    public function sendHTTPStatusCode($code)
    {
        if ($this->statusSent) {
            PWELogger::warn("Trying to send HTTP status more than once for code: %s", $code);
        }

        if (!preg_match("/^[12345][0-9][0-9]$/", $code)) {
            $code = 500;
        }

        if (!headers_sent()) {
            $status = $_SERVER["SERVER_PROTOCOL"] . ' ' . $code;
            PWELogger::debug("HTTP Status header: %s", $status);
            header($status, true, $code);
        } else {
            PWELogger::warn("Cannot report status, headers has been sent");
        }
        $this->statusSent = true;
    }

    public function isStatusSent()
    {
        return $this->statusSent;
    }

    public function getErrorPage(\Exception $e)
    {
        $smarty = $this->getSmarty();
        $smarty->assign('code', $e->getCode() ? $e->getCode() : HTTP5xxException::RUNTIME_ERROR);
        $smarty->assign('code_desc', PWEHTTPException::$HTTPErrorMessages[$e->getCode()]);
        $smarty->assign('message', $e->getMessage());
        if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
            $smarty->assign('trace', $e->__toString());
            $smarty->assign('inner', $e->getPrevious());
        }
        $smarty->setTemplateFile($this->errorsTemplate);
        return $smarty->fetchAll();
    }

    public function getSiteStructure()
    {
        $XML = new PWEXML($this->getTempDirectory());
        $struct = array();
        $XML->FileToArray($this->siteStructureFile, $struct);
        return $struct;
    }

    /**
     *
     * @param mixed $tag
     * @return string
     */
    public function getContent($tag = false)
    {
        return $this->htmlContent[$tag];
    }

    public function addContent(SmartyWrapper $smarty, $tag = false)
    {
        $this->htmlContent[$tag] .= $smarty->fetchAll();
        PWELogger::debug("Content %s [%d]: %s...", $tag, strlen($this->htmlContent[$tag]), substr($this->htmlContent[$tag], 0, 64));
    }

    /**
     * Returns array of hierarchical arrays for site structure level $level.
     * Array chosen by current page path in site structure and current pages
     * have key 'selected' set to true
     * @param int $level level to return
     * @return array
     */
    public function getStructLevel($level)
    {
        PWELogger::debug("Building struct level %s", $level);
        $matched = $this->getURL()->getMatchedAsArray();
        if ($level > sizeof($matched)) {
            if (sizeof($this->getURL()->getParamsAsArray())) {
                $module = $this->getCurrentModuleInstance();
                if ($module instanceof MenuGenerator) {
                    PWELogger::debug('Building menu via current module');
                    return $module->getMenuLevel($level);
                }
            }
            return array();
        } else {
            $levelCount = 0;
            $current = & $this->siteStructure['url'];
            while ($levelCount <= $level) {
                $pos = PWEXMLFunctions::findNodeWithAttributeValue($current, 'link', $matched[$levelCount]);
                if ($pos < 0) {
                    //throw new HTTP5xxException("Something gone completely wrong with the structure");
                    break;
                }

                $current[$pos]['selected'] = true;
                //PWELogger::debug("I", $current[$pos]);

                if ($levelCount == $level)
                    break;

                $current = & $current[$pos]['!c']['url'];
                $levelCount++;
            }
            //PWELogger::debug("Final array: ", $current);
            return $current;
        }
    }

    /**
     * @param $header_name
     * @return string header value
     */
    public function getHeader($header_name)
    {
        return $_SERVER["HTTP_" . strtoupper(str_replace('-', '_', $header_name))];
    }

    public function setStaticDirectory($dir)
    {
        $this->staticFolder = $dir;
    }

    public function getStaticDirectory()
    {
        return $this->staticFolder;
    }

    public function getStaticHref()
    {
        return $this->staticHref;
    }

    public function setStaticHref($href)
    {
        $this->staticHref = $href;
    }

    /**
     *  Jump to first child feature
     */
    private function jumpToFirstChild()
    {
        $eg_node = $this->getNode();
        if (isset($eg_node['!a']['class'])
            || sizeof($this->URL->getParamsAsArray())
            || !isset($eg_node['!c']['url'])
        ) {
            return;
        }

        foreach ($eg_node['!c']['url'] as $v) {
            PWELogger::info('Jump To First Сhild: %s', $v['!a']['link']);
            $jumpTo = $v['!a']['link'] . '/';
            if (isset($_SERVER["QUERY_STRING"]) && strlen($_SERVER["QUERY_STRING"])) {
                $jumpTo .= $_SERVER["QUERY_STRING"];
            }
            throw new HTTP3xxException($jumpTo, HTTP3xxException::REDIRECT);
        }
    }


}

?>