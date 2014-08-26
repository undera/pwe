<?php

namespace PWE\Exceptions;

use RuntimeException;

/**
 * Description of PHPFatalException
 *
 * @author undera
 */
class PHPFatalException extends RuntimeException
{

    public static function activate()
    {
        return set_error_handler("PWE\Exceptions\PHPFatalException::errorHandler", E_ALL ^ E_NOTICE);
    }

    public function __construct($errstr, $errno, $errfile, $errline)
    {
        parent::__construct("PHP Error with code $errno: $errstr", 500);
        $this->file = $errfile;
        $this->line = $errline;
    }

    public function __toString()
    {
        $res = parent::__toString();
        $msg = "PHP Error on " . $this->file . ':' . $this->line;
        return $msg . "\n" . $res;
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // @ prefix sets error_reporting() to 0, avoid triggering on @fopen
        if (error_reporting() != 0)
            throw new PHPFatalException($errstr, $errno, $errfile, $errline);
    }

}

?>