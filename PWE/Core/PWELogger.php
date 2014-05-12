<?php

namespace PWE\Core;

abstract class PWELogger
{

    const DEBUG = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const NONE = 4;

    private static $stderr = "php://stderr";
    private static $stdout = "php://stderr";
    private static $pwe_debug_level = self::WARNING;

    public static function getLevelByName($levelName)
    {
        // three letters to allow shotrhands
        $levelName = strtolower(substr($levelName, 0, 3));
        switch ($levelName) {
            case "deb":
                return self::DEBUG;
            case "inf":
                return self::INFO;
            case "war":
                return self::WARNING;
            case "err":
                return self::ERROR;
            case "non":
                return self::NONE;
            default:
                return self::INFO;
        }
    }

    public static function setStdErr($file)
    {
        self::$stderr = $file;
    }

    public static function setStdOut($file)
    {
        self::$stdout = $file;
    }

    static function setLevel($level)
    {
        self::$pwe_debug_level = $level;
        PWELogger::debug("Switched to log level %d", $level);
    }

    static function getLevel()
    {
        return self::$pwe_debug_level;
    }

    // date time id path level msg
    private static function debug_print($file, $level, $format, $data)
    {
        array_shift($data);
        foreach ($data as $k => $v) {
            if (is_string($v)) {
                $data[$k] = str_replace("\n", ' ', $v);
            } elseif ($v instanceof \Exception) {
                $data[$k] = $v->__toString();
            } elseif (!is_numeric($v)) {
                $data[$k] = str_replace("\n", " ", print_r($v, true));
            }
        }

        $mtime = microtime(true);
        $time = 1000 * ($mtime - intval($mtime));

        $trace = debug_backtrace();
        $location = $trace[2]['function'];
        for ($n = 2; $n < sizeof($trace); $n++) {
            if (isset($trace[$n]['class'])) {
                $location = $trace[$n]['class'];
                break;
            }
        }

        $id = $_SERVER['REMOTE_PORT'] ? $_SERVER['REMOTE_PORT'] : getmypid();

        $msg = sizeof($data) ? vsprintf($format, $data) : $format;
        error_log(sprintf("[%s.%3d %s %s %s] %s\n", date('d.m.Y H:m:s'), $time, $id, $location, $level, $msg), 3, $file);
    }

    static function debug($msg)
    {
        if (self::$pwe_debug_level > PWELogger::DEBUG)
            return;

        $data = func_get_args();
        self::debug_print(self::$stdout, "debug", $msg, $data);
    }

    static function info($msg)
    {
        if (self::$pwe_debug_level > PWELogger::INFO)
            return;

        $data = func_get_args();
        self::debug_print(self::$stdout, "info", $msg, $data);
    }

    static function warn($msg)
    {
        if (self::$pwe_debug_level > PWELogger::WARNING)
            return;

        $data = func_get_args();
        self::debug_print(self::$stderr, "warn", $msg, $data);
    }

    static function error($msg)
    {
        if (self::$pwe_debug_level > PWELogger::ERROR)
            return;

        $data = func_get_args();
        self::debug_print(self::$stderr, "error", $msg, $data);
    }

}

?>