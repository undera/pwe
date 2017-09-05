<?php

namespace PWE\Core;

use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Lib\Smarty\SmartyAssociative;
use PWE\Utils\PWEXMLFunctions;

class PWEURL implements SmartyAssociative
{

    protected $node;
    private $URLArrayMatched = array();
    private $URLArrayParams = array();
    private $URLArray;
    private $URL;
    private $baseDirectory;
    private $failure;
    private $hadTrailingSlash = false;

    public function __construct($uri, &$structure)
    {
        $this->parseURL($uri);
        $this->detectSubdirectory();

        $this->node = array('!c' => $structure, '!i' => array());
        $this->recursiveNodeSearch($this->getFullAsArray());
        PWELogger::debug("Done URL to structure matching: %s / %s", $this->node['!a'], $this->node['!i']);
    }

    private function recursiveNodeSearch(array $search_uri)
    {
        $link = reset($search_uri);

        $ix = PWEXMLFunctions::findNodeWithAttributeValue($this->node['!c']['url'], 'link', $link);

        if ($ix >= 0) {
            $this->URLArrayMatched[] = array_shift($search_uri);

            $inherited_attrs = $this->node['!i'];
            $this->node = &$this->node['!c']['url'][$ix];
            $this->node['!i'] = (isset($this->node['!a']) ? $this->node['!a'] : array()) + $inherited_attrs;

            if ($search_uri && isset($this->node['!c']['url'])) {
                $this->recursiveNodeSearch($search_uri);
                return;
            }
        }

        PWELogger::debug("Search: %s", $search_uri);
        if ($search_uri && isset($this->node['!c']['params'])) {
            $paramsNode =& $this->node['!c']['params'][0];
            PWELogger::debug("Params branch");

            $count = $paramsNode['!a']['count'];
            if (!$count) {
                $count = 1;
            }

            for ($n = 0; $search_uri && $n < $count; $n++) {
                $this->URLArrayParams[] = array_shift($search_uri);
            }

            $inherited_attrs = $this->node['!i'];
            $paramsNode['!i'] = (isset($paramsNode['!a']) ? $paramsNode['!a'] : array()) + $inherited_attrs;
            if ($search_uri && isset($paramsNode['!c']['url'])) {
                $this->node = &$paramsNode;
                $this->recursiveNodeSearch($search_uri);
                return;
            } else {
                $this->node['!i'] = $paramsNode['!i'];
            }
        }

        if (end($this->URLArray) && !$this->hadTrailingSlash) {
            if ($this->isForcedTrailingSlash()) {
                if (!strstr(end($this->URLArray), '.')) {
                    $url = $this->URL . '/';
                    if ($_GET) {
                        $url .= '?' . http_build_query($_GET);
                    }
                    PWELogger::debug("Node attributes: %s", $this->node['!i']);
                    throw new HTTP3xxException($url, HTTP3xxException::PERMANENT);
                }
            }
        }

        // check params count
        if ($search_uri && isset($this->node['!i']['accept'])) {
            for ($n = 0; $search_uri && $n < $this->node['!i']['accept']; $n++) {
                $this->URLArrayParams[] = array_shift($search_uri);
            }
        }

        if (sizeof($search_uri)) {
            if (strlen(end($search_uri)) || sizeof($search_uri) > 1) {
                $this->failure = new HTTP4xxException("Requested page not found", HTTP4xxException::NOT_FOUND);
            }
        }

    }

    private function parseURL($uri)
    {
        if ($uri[0] != '/') {
            throw new HTTP4xxException('URL must start with /', HTTP4xxException::BAD_REQUEST);
        }

        $uri = parse_url($uri);
        if (!$uri) {
            throw new HTTP4xxException("Requested URI incorrect", HTTP4xxException::BAD_REQUEST);
        }

        $this->URL = urldecode($uri['path']);
        $this->URLArray = explode('/', $this->URL);

        if (in_array('..', $this->URLArray) || in_array('.', $this->URLArray) || strstr($this->URL, '//')) {
            $goto = str_replace('/../', '/', $this->URL);
            $goto = str_replace('/.', '', $goto);
            $goto = str_replace('//', '/', $goto);
            throw new HTTP3xxException($goto, HTTP3xxException::PERMANENT);
        }

        while (sizeof($this->URLArray) > 1 && !strlen(end($this->URLArray))) {
            $this->hadTrailingSlash = true;
            array_pop($this->URLArray);
        }
    }

    private function detectSubdirectory()
    {
        $docroot = explode(DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT']);
        $script_dir = explode(DIRECTORY_SEPARATOR, dirname($_SERVER['SCRIPT_FILENAME']));

        foreach ($docroot as $k => $docroot_item) {
            if ($script_dir[$k] != $docroot_item) {
                PWELogger::warn("SCRIPT_FILENAME points outside of DOCUMENT_ROOT");
                return;
            } else {
                if (strlen($docroot_item)) {
                    unset($script_dir[$k]);
                }
            }
        }

        $this->baseDirectory = implode('/', $script_dir);

        if (strlen($this->baseDirectory)) {
            $this->baseDirectory = str_replace('\\', '/', $this->baseDirectory);
            foreach (explode('/', $this->baseDirectory) as $k => $v) {
                if ($k) {
                    unset($this->URLArray[$k]);
                }
            }
            $this->URLArray = array_values($this->URLArray);
        }
    }

    public function getFullAsArray()
    {
        return $this->URLArray;
    }

    public function getParamsAsArray()
    {
        return $this->URLArrayParams;
    }

    public static function getSmartyAllowedMethods()
    {
        return array('getFullCount', 'getMatchedCount', 'getParamsCount',
            'getFullAsArray', 'getMatchedAsArray', 'getParamsAsArray',);
    }

    /**
     * helper method for smarty
     * @return int
     */
    public function getFullCount()
    {
        return sizeof($this->URLArray);
    }

    public function getMatchedAsArray()
    {
        return $this->URLArrayMatched;
    }

    public function getMatchedCount()
    {
        return sizeof($this->URLArrayMatched);
    }

    public function getParamsCount()
    {
        return sizeof($this->URLArrayParams);
    }

    public function &getNode()
    {
        if (!$this->node) {
            throw new HTTP5xxException("Current node was not defined yet. Method setURL must be called before getting current Node");
        }
        return $this->node;
    }

    public function getFailure()
    {
        return $this->failure;
    }


    /**
     * @return bool
     */
    public function isHadTrailingSlash()
    {
        return $this->hadTrailingSlash;
    }

    /**
     * @return bool
     */
    public function isForcedTrailingSlash()
    {
        return $this->node['!i']['force_trailing_slash'] || !isset($this->node['!i']['force_trailing_slash']);
    }
}
