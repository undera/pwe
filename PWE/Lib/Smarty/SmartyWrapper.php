<?php

namespace PWE\Lib\Smarty;

use \Smarty;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Modules\Setupable;
use PWE\Utils\FilesystemHelper;

class SmartyWrapper extends Smarty implements Setupable {

    private $templateFile;
    private $PWE;

    public function __construct(PWECore $PWE) {
        parent::__construct();
        $this->PWE = $PWE;
        $smartyDir = $this->PWE->getTempDirectory();
        $settings = $this->PWE->getModulesManager()->getModuleSettings(__CLASS__);
        $settings = $settings['!c']['settings'][0];
        if ($settings['!a']['cacheDir'])
            $smartyDir = $settings['!a']['cacheDir'];
        PWELogger::debug('Smarty cache dir detected as ' . $smartyDir);
        $this->setCacheDir($smartyDir);
        $this->setCompileDir($smartyDir);
        $this->registerPlugin('function', 'flush', 'flush', false);
        $this->registerPlugin('function', 'sleep', 'sleep', false); // for debug purposes
    }

    public function setTemplateFile($file) {
        $this->templateFile = $file;
    }

    public function fetchAll() {
        PWELogger::debug("Fetch all with tpl: " . $this->templateFile);
        return $this->fetch($this->templateFile);
    }

    /**
     *
     * @param string $object_name
     * @param SmartyAssociative $object 
     */
    public function registerObject($object_name, $object) {
        parent::registerObject($object_name, $object, $object->getSmartyAllowedMethods(), false);
    }

    // FIXME: why here?
    public static function setup(PWECore $pwe, array &$registerData) {
        PWELogger::debug("Copying into " . $pwe->getStaticDirectory() . '/design');
        FilesystemHelper::fsys_copydir(dirname(__FILE__) . '/../../design', $pwe->getStaticDirectory() . '/design');
    }

}

?>