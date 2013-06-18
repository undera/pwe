<?php

namespace PWE\Modules\RedirectAccepted;

use PWE\Exceptions\HTTP3xxException;
use PWE\Exceptions\HTTP4xxException;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;

class RedirectAccepted extends PWEModule implements Outputable {

    public function process() {
        $node = $this->PWE->getNode();
        $params = $this->PWE->getURL()->getParamsAsArray();

        if (!isset($node['!a']['base']))
            throw new HTTP4xxException("Redirect not configured", HTTP4xxException::PRECONDITION_FAILED);

        $uri = $node['!a']['base'];

        // добавление параметров
        if (sizeof($params)) {
            if (strrchr($uri, '/') != '/')
                $uri .= '/';
            $uri .= implode('/', $params);
        }

        if (!strstr(end($params), '.') && !$node['!a']['no_final_slash']) {
            $uri.="/";
        }

        // добавление trailer
        if ($node['!a']['trailer'])
            $uri .= $node['!a']['trailer'];

        // проверка наличия запросной части в URI
        if ($_GET) {
            $uri .= '?' . http_build_query($_GET);
        }

        // what this is for?
        $uri = preg_replace('!([^:])/{2,}!', '\\1/', $uri);

        // переадресация
        throw new HTTP3xxException($uri, HTTP3xxException::PERMANENT);
    }

}

?>