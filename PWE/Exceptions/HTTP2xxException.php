<?php

namespace PWE\Exceptions;

class HTTP2xxException extends PWEHTTPException {

    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;

    private $usingTemplate = false;

    function __construct($message, $code = self::OK) {
        parent::__construct($message, $code);
    }

    public function isUsingTemplate() {
        return $this->usingTemplate;
    }

    public function setUsingTemplate($flag) {
        $this->usingTemplate = $flag;
    }

}

?>