<?php

namespace PWE\Modules\RedirectAccepted;

use PWE\Modules\PWEModule;
use PWE\Modules\Outputable;

use PWE\Exceptions\HTTP4xxException;
use PWE\Exceptions\HTTP3xxException;
/**
 * Description of RedirectAccepted
 *
 * @author undera
 */
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
            $uri .= implode('/', $params) . (strstr(end($params), '.') ? '' : '/');
        }

        // добавление trailer
        if ($node['!a']['trailer'])
            $uri .= $node['!a']['trailer'];

        // проверка наличия запросной части в URI
        if (sizeof($_GET)) {
            foreach ($_GET as $k => $v)
                $query[] = "$k=" . rawurlencode($v);
            ;
            $uri .= '?' . implode('&', $query);
        }
        $uri = preg_replace('!([^:])/{2,}!', '\\1/', $uri);


        // переадресация
        throw new HTTP3xxException($uri, HTTP3xxException::PERMANENT);
    }

}

?>