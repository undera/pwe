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
    private $URLArray;
    private $URL;
    private $baseDirectory;

    public function __construct($uri, &$structure)
    {
        $this->parseURL($uri);
        $this->detectSubdirectory();
        $this->URLArrayParams = $this->URLArray;


        $tmpNode = array('!c' => &$structure);
        $tmpNode['!i'] = array();
        $this->structureNode = $this->recursiveNodeSearch($tmpNode, $this->getFullAsArray());

        // calculating params and match
        if (isset($this->structureNode['!p']) && is_array($this->structureNode['!p'])) {
            $nodePointer = array('!p' => &$this->structureNode);
            $depth = 0;
        } else {
            $nodePointer = & $this->structureNode;
            $depth = 1;
        }

        do {
            $nodePointer = & $nodePointer['!p'];
            $depth++;
        } while ($nodePointer);

        $this->setMatchedDepth($depth - 1);

        // check params count
        if (isset($this->structureNode['!i']['accept'])) {
            if (sizeof($this->getParamsAsArray()) > $this->structureNode['!i']['accept']) {
                PWELogger::warn("Defined accept limit %s has been exceeded: %s", $this->structureNode['!i']['accept'], sizeof($this->getParamsAsArray()));
                throw new HTTP4xxException('URI parameters count exceeded', HTTP4xxException::BAD_REQUEST);
            }
        } else {
            if (sizeof($this->getParamsAsArray())) {
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
            $this->URLArray = array_values($this->URLArray); // чиним сбитую нумерацию
        }
    }

    private function recursiveNodeSearch(array &$node, array $search_uri)
    {
        $link = array_shift($search_uri);
        PWELogger::debug("Trying link: %s", $link);

        $ix = PWEXMLFunctions::findNodeWithAttributeValue($node['!c']['url'], 'link', $link);

        if ($ix >= 0) {
            $inherited_attrs = $node['!i'];

            $node = & $node['!c']['url'][$ix];
            $node['!i'] = $inherited_attrs + (isset($node['!a']) ? $node['!a'] : array());

            if (isset($node['!c']['url']) || isset($node['!c']['params'])) {
                return $this->recursiveNodeSearch($node, $search_uri);
            }
        }

        if ($search_uri) {

        }

        PWELogger::debug("Done URL to structure matching");
        return $node;
    }

    public function getFullAsArray()
    {
        return $this->URLArray;
    }

    public function setMatchedDepth($depth)
    {
        for ($n = 0; $n < $depth; $n++) {
            array_push($this->URLArrayMatched, array_shift($this->URLArrayParams));
        }
        PWELogger::debug("Matched depth %s, params: %s", $depth, $this->URLArrayParams);
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

    public static function protectAgainsRelativePaths($path)
    {
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