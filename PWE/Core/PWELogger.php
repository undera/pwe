<?php

namespace PWE\Core;

use \Exception;

abstract class PWELogger {

    const DEBUG = 0;
    const INFO = 1;
    const WARNING = 2;
    const ERROR = 3;
    const NONE = 4;

    private static $stderr = "php://stderr";
    private static $stdout = "php://stderr";
    private static $pwe_debug_level = self::INFO;

    public static function getLevelByName($levelName) {
        // three letters to allow shotrhands
        $levelName = strtolower(substr($levelName, 0, 3));
        switch ($levelName) {
            case "deb": return self::DEBUG;
            case "inf": return self::INFO;
            case "war": return self::WARNING;
            case "err": return self::ERROR;
            case "non": return self::NONE;
            default: return self::INFO;
        }
    }

    public static function setStdErr($file) {
        self::$stderr = $file;
    }

    public static function setStdOut($file) {
        self::$stdout = $file;
    }

    static function setLevel($level) {
        self::$pwe_debug_level = $level;
        PWELogger::debug("Switched to log level $level\n");
    }

    static function getLevel() {
        return self::$pwe_debug_level;
    }

    // TODO: refactor me!
    private static function debug_print($data, $file, Exception $e = null) {
        $mtime = explode('.', microtime(true));
        $time = date('d.m.Y H:m:s') . '.' . sprintf('%0-4s', end($mtime));
        $trace = debug_backtrace();

        $location = @$trace[2]['function'];

        for ($n = 2; $n < sizeof($trace); $n++) {
            if (isset($trace[$n]['class'])) {
                $location = $trace[$n]['class'];
                break;
            }
        }

        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if ($name)
                    self::debug_print($prefix . $name . '["' . $k . '"]' . " => " . $v, $file);
                else
                    self::debug_print($prefix . $k . " => " . $v, $file);

                if (is_array($v))
                    foreach ($v as $kk => $vv) {
                        if (is_object($vv))
                            self::debug_print($prefix . "   " . $kk . " => " . get_class($vv), $file);
                        else
                            self::debug_print($prefix . "   " . $kk . " => " . $vv, $file);
                    }
            }
        }else if ($data instanceof Exception) {
            self::debug_print($data->__toString(), $file);
        } else {
            //$data=str_replace("\n", "\t", $data);
            $id = $_SERVER['REMOTE_PORT'] ? $_SERVER['REMOTE_PORT'] : getmypid();
            file_put_contents($file, "[$time $id $location $data\n");
        }

        if ($e != null) {
            self::debug_print($e->__toString(), $file);
        }
    }

    static function debug($msg, $object = NULL, $indent = 0) {
        if ($indent >= 2)
            return;

        if (self::$pwe_debug_level > PWELogger::DEBUG)
            return;

        if ($msg) {
            $str = "debug]" . str_repeat("\t", $indent + 1) . str_replace("\n", " ", $msg);
            PWELogger::debug_print($str, self::$stdout);
        }

        if (isset($object)) {
            if (is_array($object)) {
                PWELogger::debug_print($object, self::$stdout);
            } else if ($object instanceof \Exception) {
                PWELogger::debug($object);
            } else {
                PWELogger::debug("Object: " . gettype($object));
            }
        }
    }

    static function info($msg) {
        if (self::$pwe_debug_level > PWELogger::INFO)
            return;

        $str = "info]\t$msg";
        PWELogger::debug_print($str, self::$stdout);
    }

    static function warn($msg, Exception $e = null) {
        self::warning($msg, $e);
    }

    static function warning($msg, Exception $e = null) {
        if (self::$pwe_debug_level > PWELogger::WARNING)
            return;

        $str = "warn]\t$msg";
        PWELogger::debug_print($str, self::$stderr, $e);
    }

    static function error($msg, Exception $e = null) {
        if (self::$pwe_debug_level > PWELogger::ERROR)
            return;

        $str = "error]\t$msg";
        PWELogger::debug_print($str, self::$stderr, $e);
    }

}

// default level is info
PWELogger::setLevel(PWELogger::WARNING);
?>