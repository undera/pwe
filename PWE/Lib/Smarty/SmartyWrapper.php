<?php

namespace PWE\Lib\Smarty;

use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Modules\Setupable;
use PWE\Utils\FilesystemHelper;
use Smarty;

class SmartyWrapper extends Smarty implements Setupable
{

    private $templateFile;
    private $PWE;

    public function __construct(PWECore $PWE)
    {
        parent::__construct();
        $this->PWE = $PWE;
        $smartyDir = $this->PWE->getTempDirectory();
        $settings = $this->PWE->getModulesManager()->getModuleSettings(__CLASS__);
        $settings = $settings['!c']['settings'][0];
        if ($settings['!a']['cacheDir'])
            $smartyDir = $settings['!a']['cacheDir'];
        PWELogger::debug('Smarty cache dir detected as %s', $smartyDir);
        $this->setCacheDir($smartyDir);
        $this->setCompileDir($smartyDir);
        $this->registerPlugin('function', 'flush', 'flush', false);
        $this->registerPlugin('function', 'sleep', 'sleep', false); // for debug purposes
    }

    public function setTemplateFile($file)
    {
        $this->templateFile = $file;
    }

    public function fetchAll()
    {
        PWELogger::debug("Fetch all with tpl: %s", $this->templateFile);
        return $this->fetch($this->templateFile);
    }

    /**
     *
     * @param string $object_name
     * @param object $object_impl
     * @param array $allowed
     * @param bool $smarty_args
     * @param array $block_methods
     * @internal param \PWE\Lib\Smarty\SmartyAssociative $object
     * @return \Smarty_Internal_TemplateBase|void
     */
    public function registerObject($object_name, $object_impl, $allowed = array(), $smarty_args = true, $block_methods = array())
    {
        parent::registerObject($object_name, $object_impl, $object_impl->getSmartyAllowedMethods(), false);
    }

    // FIXME: why here?
    public static function setup(PWECore $pwe, array &$registerData)
    {
        PWELogger::debug("Copying into %s/design", $pwe->getStaticDirectory());
        FilesystemHelper::fsys_copydir(__DIR__ . '/../../design', $pwe->getStaticDirectory() . '/design');
    }

}
