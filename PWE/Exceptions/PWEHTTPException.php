<?php

namespace PWE\Exceptions;

use \RuntimeException;
use PWE\Core\PWELogger;

abstract class PWEHTTPException extends RuntimeException {

    public static $HTTPErrorMessages = array(
        0 => 'Application Level Error',
        200 => 'OK',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        412 => 'Precondition Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        503 => 'Temporarily Unavailable',
    );

    function __construct($message, $code, \Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
        PWELogger::debug("Exception $code: " . ($code == 200 ? strlen($message) . " bytes to display" : $message));
    }

}

?>