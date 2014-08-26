<?php

namespace PWE\Exceptions;

class HTTP5xxException extends PWEHTTPException
{

    const RUNTIME_ERROR = 500;
    const UNIMPLEMENTED = 501;
    const UNAVAILABLE = 503;
    const TIMEOUT = 504;

    function __construct($message, $code = HTTP5xxException::RUNTIME_ERROR)
    {
        parent::__construct($message, $code);
    }

}

?>