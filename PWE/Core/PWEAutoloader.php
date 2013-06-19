<?php

namespace PWE\Core;

use PWE\Core\PWELogger;
use RuntimeException;

abstract class PWEAutoloader {

    /**
     *
     * @var PWECore
     */
    private static $core;
    //
    private static $try_loading_cache = true;
    private static $cache = array();
    private static $sourceRoots = array();

    private static function readdir($dir) {
        $res = array();
        $dirHandle = opendir($dir);
        while ($file = readdir($dirHandle)) {
            if (is_dir($dir . '/' . $file))
                $res[] = $file;
        }

        closedir($dirHandle);
        return $res;
    }

    public static function activate() {
        spl_autoload_register("PWE\Core\PWEAutoloader::doIt");
    }

    public static function setPWE(AbstractPWECore $pwe) {
        PWELogger::debug("Set PWE core");
        self::$core = $pwe;
        if (self::getCacheFile()) {
            self::loadCache();
        }
    }

    public static function addSourceRoot($path) {
        $path = realpath($path);
        if (!is_dir($path)) {
            PWELogger::warn("Path not exists: " . $path);
        } else {
            self::$sourceRoots[] = $path;
        }
    }

    public static function doIt($name) {
        $res = self::autoloadClassFromCache($name) || self::seekAll($name) || self::seekIncludes($name) || self::seekBacktraces($name);
        if (!$res || (!class_exists($name) && !interface_exists($name))) {
            PWELogger::warning("Class not found: " . $name);
            return false;
        }
        return true;
    }

    private static function autoloadClassFromCache($name) {
        PWELogger::debug("Searching class $name in cache");
        self::loadCache();

        if (self::$cache) {
            if (isset(self::$cache[$name])) {
                return self::loadFile(self::$cache[$name]);
            }
        }
        return false;
    }

    private static function putToCache($name, $path) {
        PWELogger::info('Putting to cache path for ' . $name);
        $cache_file = self::getCacheFile();

        $path = realpath($path);
        $oldpath = self::$cache[$name];
        PWELogger::debug("Old path was: " . $oldpath . " / new path: " . $path);
        if ($oldpath != $path) {
            PWELogger::info("Module path changed for $name, needs re-register: $path");
            if (isset(self::$core)) {
                self::$core->getModulesManager()->registerModule($name);
            }

            self::$cache[$name] = $path;
            PWELogger::debug("Classpath cache file: " . $cache_file);
            if ($cache_file) {
                file_put_contents($cache_file, serialize(self::$cache));
            }
            return true;
        }

        return false;
    }

    private static function seekBacktraces($name) {
        PWELogger::debug("Searching class $name in backtrace");

        $trace = debug_backtrace();
        // is it somewhere near us?
        foreach ($trace as $trace_el) {
            if (!isset($trace_el['file'])) {
                PWELogger::debug("Backtrace element does not have file for class $name");
                continue;
            }

            $path = dirname($trace_el['file']);
            if (self::loadClassFromPath($name, $path)) {
                return true;
            }
        }

        PWELogger::info("PWE Class not found in backtrace paths: $name");
        return false;
    }

    private static function seekIncludes($name) {
        PWELogger::debug("Searching class $name in included paths");

        $files = array();
        foreach (get_included_files() as $fname) {
            //PWELogger::debug("$fname");
            $files[] = dirname($fname);
        }

        // is it somewhere near us?
        // TODO: better security here!
        foreach (array_unique($files) as $path) {
            if (self::load2Variants($name, $path))
                return true;
        }

        PWELogger::info("Class not found in includes: $name");
        return false;
    }

    private static function seekAll($name) {
        PWELogger::debug("Searching class $name in whole sources");
        $paths = self::$sourceRoots;

        while ($dir = array_shift($paths)) {
            //PWELogger::debug("Looking in $dir");
            if (self::load2Variants($name, $dir))
                return true;

            $files = self::readdir($dir);
            foreach ($files as $file) {
                if ($file[0] == '.')
                    continue;
                $paths[] = realpath($dir . '/' . $file);
            }
        }
        PWELogger::info("PWE Class not found in all source: $name");

        return false;
    }

    private static function loadClassFromPath($name, $path) {
        //PWELogger::debug("Searching class $name in $path");
        if (self::$core) {
            $srcs = self::$sourceRoots;

            foreach ($srcs as $src) {
                // here we require core folder to be placed under root
                while (strpos($path, $src) !== false) {
                    if (self::load2Variants($name, $path))
                        return true;
                    $path = dirname($path);
                }
            }
        }
        return false;
    }

    private static function load2Variants($name, $path) {
        $nameChanged = str_replace("\\", "/", $name);
        if (self::loadFile($path . '/' . $nameChanged . ".class.php")) {
            self::putToCache($name, $path . '/' . $nameChanged . ".class.php");
            return true;
        }
        if (self::loadFile($path . '/' . $nameChanged . ".php")) {
            self::putToCache($name, $path . '/' . $nameChanged . ".php");
            return true;
        }
        if (self::loadFile($path . '/' . strtolower($nameChanged) . ".php")) {
            self::putToCache($name, $path . '/' . strtolower($nameChanged) . ".php");
            return true;
        }

        if (!strstr($nameChanged, '/')) {
            $nameChangedUnderscores = str_replace("_", "/", $name);
            if (self::loadFile($path . '/' . $nameChangedUnderscores . ".php")) {
                self::putToCache($name, $path . '/' . $nameChangedUnderscores . ".php");
                return true;
            }
        }
        return false;
    }

    private static function loadFile($fname) {
        $fname = str_replace("\\", "/", $fname);
        if (is_file($fname)) {

            PWELogger::debug("Autoloading $fname");
            require_once $fname;
            return true;
        } else {
            return false;
        }
    }

    private static function getCacheFile() {
        if (self::$core) {
            if (is_writable(self::$core->getTempDirectory())) {
                return self::$core->getTempDirectory() . '/pwe_classpath_cache.' . posix_geteuid();
            } else {
                PWELogger::debug("Cache directory not writeable: " . self::$core->getTempDirectory());
            }
        }
        return false;
    }

    private static function loadCache() {
        // lazy load cache if needed
        if (self::$try_loading_cache) {
            $cache_file = self::getCacheFile();
            if (is_file($cache_file)) {
                PWELogger::debug("Loading classpath cache from: " . $cache_file);
                self::$cache = array_merge(self::$cache, unserialize(file_get_contents($cache_file)));
                self::$try_loading_cache = false;
            } else {
                if ($cache_file) {
                    PWELogger::warning("No classpath cache loaded: " . $cache_file);
                } else {
                    PWELogger::debug("No classpath cache loaded: " . $cache_file);
                }
                self::$cache = array();
                self::$try_loading_cache = true;
            }
        }
    }

}

?>