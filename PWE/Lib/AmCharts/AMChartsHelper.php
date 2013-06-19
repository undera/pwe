<?php

namespace PWE\Lib\AmCharts;

use PWE\Modules\Setupable;
use PWE\Modules\PWEModule;
use PWE\Utils\FilesystemHelper;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP5xxException;

/**
 * Description of AMChartsHelper
 *
 * @author undera
 */
class AMChartsHelper extends PWEModule implements Setupable {

    public static function getAmChartsDir() {
        $dirs = FilesystemHelper::fsys_readdir(self::getPathTo(), true, FALSE, '\PWE\Lib\AmCharts\isAmchartsBase');
        if (!$dirs) {
            throw new HTTP5xxException("Can't find any Amcharts lib in " . self::getPathTo());
        }
        return self::getPathTo() . '/' . current(array_keys($dirs));
    }

    public static function getPathTo() {
        return dirname(__FILE__);
    }

    public static function setup(PWECore $pwe, array &$regData) {
        PWELogger::info("Copying files for amcharts");

        $basedir = self::getAmChartsDir();
        if (!$basedir) {
            throw new HTTP5xxException("Cannot find amcharts_x.x.x dir $basedir");
        }
        $copied = FilesystemHelper::fsys_copydir($basedir . '/amcharts', $pwe->getStaticDirectory() . '/amcharts/javascript', true);
        if (!$copied) {
            throw new HTTP5xxException("Failed to copy amcharts resource files, see log for details");
        }
        return true;
    }

}

function isAmchartsBase($name) {
    $res = preg_match('/amcharts[_-]\d+\.\d+\.\d+$/', $name);
    return $res;
}

?>