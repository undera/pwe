<?php
namespace PWE\Exceptions;

class HTTP4xxException extends PWEHTTPException
{
    const BAD_REQUEST = 400;
    const PAYMENT_REQUIRED = 402;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const PRECONDITION_FAILED = 412;

    function __construct($message, $code = HTTP4xxException::BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }

}

?>