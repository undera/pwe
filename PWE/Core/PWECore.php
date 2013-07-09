<?php

namespace PWE\Core;

use BadFunctionCallException;
use PWE\Auth\PWEUserAuthController;
use PWE\Exceptions\HTTP2xxException;
use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Exceptions\PWEHTTPException;
use PWE\Lib\Smarty\SmartyAssociative;
use PWE\Lib\Smarty\SmartyWrapper;
use PWE\Modules\MenuGenerator;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Modules\PWEModulesManager;
use PWE\Utils\PWEXML;
use PWE\Utils\PWEXMLFunctions;
use RuntimeException;

class PWECore extends AbstractPWECore implements SmartyAssociative {

    /**
     *
     * @var PWEURL
     */
    private $URL;
    protected $structureNode = array();
    protected $siteStructureFile;
    private $statusSent = false;
    private $siteStructure;
    private $htmlContent = '';
    private $errorsTemplate;

    /**
     *
     * @var PWEModulesManager
     */
    protected $modulesManager;

    /**
     *
     * @var PWEModule
     */
    private $currentModuleInstance;

    public function __construct() {
        PWELogger::debug("Creating PWE Core");
        parent::__construct();
        $this->errorsTemplate = 'error.tpl';
    }

    public function process($uri) {
        try {
            $this->createModulesManager();
            $this->setURL($uri);
            $this->currentModuleInstance = $this->getModuleInstance($this->structureNode);
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
        }
    }

    public function sendHTTPStatusCode($code) {
        if ($this->statusSent) {
            PWELogger::warn("Trying to send HTTP status more than once for code: " . $code);
        }

        if (!preg_match("/^[12345][0-9][0-9]$/", $code)) {
            $code = 500;
        }

        if (!headers_sent()) {
            $status = $_SERVER["SERVER_PROTOCOL"] . ' ' . $code;
            PWELogger::debug("HTTP Status header: " . $status);
            header($status, true, $code);
        } else {
            PWELogger::warn("Cannot report status, headers has been sent: " . $status);
        }
        $this->statusSent = true;
    }

    public function isStatusSent() {
        return $this->statusSent;
    }

    public function getErrorPage(\Exception $e) {
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

    public function setXMLDirectory($dir) {
        parent::setXMLDirectory($dir);
        $this->siteStructureFile = $this->getXMLDirectory() . '/out.xml';

        if ($this->modulesManager) {
            $this->modulesManager->setRegistryFile($this->getXMLDirectory() . '/eg_globals.xml');
        }
    }

    public function getStructureFile() {
        return $this->siteStructureFile;
    }

    /**
     *
     * @param string $uri
     */
    public function setURL($uri) {
        if ($this->URL)
            throw new RuntimeException("Повторное использование метода setURL запрещено");

        PWELogger::info("=== " . $_SERVER['REQUEST_METHOD'] . " $uri");
        PWELogger::debug("Request variables", $_REQUEST);
        $this->URL = new PWEURL($uri);
        $this->siteStructure = $this->getSiteStructure();
        $this->detectStructureNode();
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

    /**
     *
     * @return PWEURL
     */
    public function getURL() {
        if (!$this->URL)
            throw new HTTP4xxException("No URL set", HTTP4xxException::BAD_REQUEST);
        return $this->URL;
    }

    /**
     * @return string
     */
    private function getHTML() {
        if (!($this->currentModuleInstance instanceof Outputable)) {
            throw new HTTP5xxException("Module class is not Outputable");
        }
        $this->currentModuleInstance->process();

        $smarty = $this->getSmarty();
        $smarty->setTemplateFile($this->getDisplayTemplate());
        $smarty->assign('node', $this->getNode());
        PWELogger::info("Processing main template: " . $this->getDisplayTemplate());
        return $smarty->fetchAll();
    }

    /**
     *
     * @param mixed $structureNode
     * @return PWEModule
     */
    public function getModuleInstance($structureNode) {
        if (is_array($structureNode))
            return $this->modulesManager->getMultiInstanceModule($structureNode);
        else
            return $this->modulesManager->getSingleInstanceModule($structureNode);
    }

    public function getSiteStructure() {
        $XML = new PWEXML($this->getTempDirectory());
        $struct = array();
        $XML->FileToArray($this->siteStructureFile, $struct);
        return $struct;
    }

    private function detectStructureNode() {
        // 3.2. Поиск в структуре запрашиваемого узла
        $tmpNode = array('!c' => &$this->siteStructure);
        $this->structureNode = $this->recursiveNodeSearch(
                $tmpNode, $this->URL->getFullAsArray());

        // calculating params and match
        $this->structureNode['!i'] = array();
        if (isset($this->structureNode['!p']) && is_array($this->structureNode['!p'])) {
            $nodePointer = array('!p' => &$this->structureNode);
            $depth = 0;
        } else {
            $nodePointer = &$this->structureNode;
            $depth = 1;
        }

        do {
            $this->structureNode['!i'] = array_merge(
                    isset($nodePointer['!a']) ? $nodePointer['!a'] : array(), $this->structureNode['!i']);
            $nodePointer = &$nodePointer['!p'];
            $depth++;
        } while ($nodePointer);

        $this->URL->setMatchedDepth($depth - 1);

        $this->setDisplayTemplate($this->structureNode['!i']['template']);

        // если работаем с параметрами - убеждаемся что их количество допустимо
        if (isset($this->structureNode['!i']['accept'])) {
            if (sizeof($this->URL->getParamsAsArray()) > $this->structureNode['!i']['accept']) {
                PWELogger::warn("Defined accept limit " . $this->structureNode['!i']['accept'] . " has been exceeded: " . sizeof($this->URL->getParamsAsArray()));
                throw new HTTP4xxException('URI parameters count exceeded', HTTP4xxException::BAD_REQUEST);
            }
        }
        // иначе ругаемся
        else {
            if (sizeof($this->URL->getParamsAsArray())) {
                throw new HTTP4xxException("Requested page not found", HTTP4xxException::NOT_FOUND);
            }
        }
    }

    // TODO: there is simplier way to do it with xml_find_attr
    // TODO: incorporate !i calculation here
    private function recursiveNodeSearch(array &$node, array $uriArray) {
        $size = sizeof(@$node['!c']['url']);
        reset($uriArray);
        $link = current($uriArray);
        PWELogger::debug("Trying link: $link");

        // перебираем дочерних
        for ($n = 0; $n < $size; $n++) {
            //PWELogger::debug("Testing " . @$node['!c']['url'][$n]['!a']['link']);
            // если совпал компонент
            if (@$node['!c']['url'][$n]['!a']['link'] == $link) {
                //PWELogger::debug("Matched #" . $n);

                $node = &$node['!c']['url'][$n];

                // если кончились элементы УРЛа - то финиш
                if (sizeof($uriArray) - 1 <= 0) {
                    break;
                }

                // углубляемся в структуру
                if (isset($node['!c']['url'])) {
                    array_shift($uriArray);
                    return $this->recursiveNodeSearch($node, $uriArray);
                }
            }
        }

        PWELogger::debug("Done URL to structure matching");
        return $node;
    }

    public function getNode() {
        if (!$this->structureNode) {
            throw new HTTP5xxException("Current node was not defined yet. Method setURL must be called before getting current Node");
        }
        return $this->structureNode;
    }

    /**
     *  Jump to first child feature
     */
    private function jumpToFirstChild() {
        $eg_node = $this->structureNode;
        if (isset($this->structureNode['!a']['class']))
            return;
        if (sizeof($this->URL->getParamsAsArray()))
            return;
        if (!isset($eg_node['!c']['url']))
            return;

        // FIXME old-style module definiton, need to decide
        if (isset($this->structureNode['!a']['mod']))
            return;

        // перебираем чайлдов в поисках подходящего
        foreach ($eg_node['!c']['url'] as $v) {
            // если дошли до сюда - значит можно джампать
            PWELogger::info('Jump To First Сhild: ' . $v['!a']['link']);
            $jumpTo = $v['!a']['link'] . '/';
            if (isset($_SERVER["QUERY_STRING"]) && strlen($_SERVER["QUERY_STRING"]))
                $jumpTo.=$_SERVER["QUERY_STRING"];
            throw new HTTP3xxException($jumpTo, HTTP3xxException::REDIRECT);
        }
    }

    /**
     *
     * @return string
     */
    public function getContent() {
        return $this->htmlContent;
    }

    public function addContent(SmartyWrapper $smarty) {
        $this->htmlContent.= $smarty->fetchAll();
        PWELogger::debug("Content[" . strlen($this->htmlContent) . "]: " . substr($this->htmlContent, 0, 64) . '...');
    }

    /**
     * Returns array of hierarchical arrays for site structure level $level.
     * Array chosen by current page path in site structure and current pages
     * have key 'selected' set to true
     * @param int $level level to return
     * @return array
     */
    public function getStructLevel($level) {
        PWELogger::debug("Building struct level $level");
        $matched = $this->getURL()->getMatchedAsArray();
        if ($level > sizeof($matched)) {
            if (sizeof($this->getURL()->getParamsAsArray())) {
                if ($this->currentModuleInstance instanceof MenuGenerator) {
                    PWELogger::debug('Building menu via current module');
                    return $this->currentModuleInstance->getMenuLevel($level);
                }
            }
            return array();
        } else {
            $levelCount = 0;
            $current = &$this->siteStructure['url'];
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

                $current = &$current[$pos]['!c']['url'];
                $levelCount++;
            }
            //PWELogger::debug("Final array: ", $current);
            return $current;
        }
    }

    protected function createModulesManager(PWEModulesManager $externalManager = null) {
        $this->modulesManager = $externalManager ? $externalManager : new PWEModulesManager($this);
    }

    /**
     * @return PWEModulesManager
     */
    public function getModulesManager() {
        if (!$this->modulesManager)
            throw new BadFunctionCallException("Not created modules manager");

        return $this->modulesManager;
    }

    public static function getSmartyAllowedMethods() {
        return array('getStructLevel', 'getContent', 'getNode', 'getCurrentModuleInstance', 'getStaticDirectory', 'getStaticHref');
    }

    public function getCurrentModuleInstance() {
        return $this->currentModuleInstance;
    }

    /**
     * @return SmartyWrapper
     */
    public function getSmarty() {
        $smarty = new SmartyWrapper($this);
        $smarty->addTemplateDir(dirname(__FILE__) . '/../tpl');
        $smarty->addTemplateDir($this->getDataDirectory() . '/tpl');
        $smarty->registerObject('PWE', $this);
        $smarty->registerObject('URL', $this->getURL());
        $smarty->registerObject('AUTH', PWEUserAuthController::getAuthControllerInstance($this));
        return $smarty;
    }

}

?>