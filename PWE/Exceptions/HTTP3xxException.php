<?php
namespace PWE\Exceptions;

use PWE\Core\PWELogger;

class HTTP3xxException extends PWEHTTPException {
    const PERMANENT = 301;
    const REDIRECT = 302;
    const NOT_MODIFIED = 304;

    function __construct($message, $code=HTTP3xxException::REDIRECT) {
        parent::__construct($message, $code);
        PWELogger::info($message, $this);
        if ($code == 301 || $code == 302) {
            if (!headers_sent()) {
                // TODO: use full URI since http 1.1 require it
                header("Location: $message");
            } else {
                PWELogger::warning("Cannot send headers to redirect to " . $message);
            }
        }
    }
}

?>