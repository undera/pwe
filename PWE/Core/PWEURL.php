<?php

namespace PWE\Core;

use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP5xxException;
use PWE\Lib\Smarty\SmartyAssociative;
use PWE\Utils\PWEXMLFunctions;

class PWEURL implements SmartyAssociative
{

    protected $structureNode;
    private $URLArrayMatched = array();
    private $URLArrayParams = array();
    private $URLArray;
    private $URL;
    private $baseDirectory;

    public function __construct($uri, &$structure)
    {
        $this->parseURL($uri);
        $this->detectSubdirectory();

        $this->structureNode = array('!c' => $structure, '!i' => array());
        $this->recursiveNodeSearch($this->getFullAsArray());
    }

    private function recursiveNodeSearch(array $search_uri)
    {
        $link = reset($search_uri);
        PWELogger::debug("Trying link: %s", $link);

        $ix = PWEXMLFunctions::findNodeWithAttributeValue($this->structureNode['!c']['url'], 'link', $link);

        if ($ix >= 0) {
            $this->URLArrayMatched[] = array_shift($search_uri);

            $inherited_attrs = $this->structureNode['!i'];

            $this->structureNode = & $this->structureNode['!c']['url'][$ix];
            $this->structureNode['!i'] = $inherited_attrs + (isset($this->structureNode['!a']) ? $this->structureNode['!a'] : array());

            if (isset($this->structureNode['!c']['url']) || isset($this->structureNode['!c']['params'])) {
                $this->recursiveNodeSearch($search_uri);
                return;
            }
        }

        if ($search_uri) {

        }

        PWELogger::debug("Done URL to structure matching");
        // check params count
        if (isset($this->structureNode['!i']['accept'])) {
            if (sizeof($search_uri) > $this->structureNode['!i']['accept']) {
                PWELogger::warn("Defined accept limit %s has been exceeded: %s", $this->structureNode['!i']['accept'], sizeof($search_uri));
                throw new HTTP4xxException('URI parameters count exceeded', HTTP4xxException::BAD_REQUEST);
            }
        } else {
            if (sizeof($search_uri)) {
                throw new HTTP4xxException("Requested page not found", HTTP4xxException::NOT_FOUND);
            }
        }

    }


    private function parseURL($uri)
    {
        if ($uri[0] != '/')
            throw new HTTP4xxException('URL must start with /', HTTP4xxException::BAD_REQUEST);

        $uri = parse_url($uri);
        if (!$uri)
            throw new HTTP4xxException("Requested URI incorrect", HTTP4xxException::BAD_REQUEST);

        $this->URL = urldecode($uri['path']);
        $this->URLArray = explode('/', $this->URL);

        if (in_array('..', $this->URLArray) || in_array('.', $this->URLArray) || strstr($this->URL, '//')) {
            $goto = str_replace('/../', '/', $this->URL);
            $goto = str_replace('/.', '', $goto);
            $goto = str_replace('//', '/', $goto);
            throw new HTTP3xxException($goto, HTTP3xxException::PERMANENT);
        }

        if (strlen(end($this->URLArray))) {
            if (!strstr(end($this->URLArray), '.')) {
                $url = $this->URL . '/';
                if ($_GET) {
                    $url .= '?' . http_build_query($_GET);
                }
                throw new HTTP3xxException($url, HTTP3xxException::PERMANENT);
            }
        } else {
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
            foreach (explode('/', $this->baseDirectory) as $k => $v)
                if ($k)
                    unset($this->URLArray[$k]);
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

    public function getNode()
    {
        if (!$this->structureNode) {
            throw new HTTP5xxException("Current node was not defined yet. Method setURL must be called before getting current Node");
        }
        return $this->structureNode;
    }

}

?>