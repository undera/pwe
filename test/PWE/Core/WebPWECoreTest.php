<?php

namespace PWE\Core;

use BadFunctionCallException;
use PWE\Modules\MenuGenerator;
use PWE\Modules\Outputable;
use PWE\Modules\PWEModule;
use PWE\Modules\PWEModulesManager;

require_once __DIR__ . '/../../PWEUnitTests.php';


class PWECoreEmul extends PWECore
{

    public $HTTPStatus;

    public function createModulesManager(PWEModulesManager $externalManager = null)
    {
        parent::createModulesManager($externalManager);
    }

    public function getModulesManager()
    {
        try {
            return parent::getModulesManager();
        } catch (BadFunctionCallException $e) {
            return null;
        }
    }

    public function sendHTTPStatusCode($code)
    {
        parent::sendHTTPStatusCode($code);
        $this->HTTPStatus = $code;
    }


}

class TestModule extends PWEModule implements Outputable, MenuGenerator
{

    public function process()
    {
        $smarty = $this->PWE->getSmarty();
        $smarty->setTemplateFile(__DIR__ . '/flat.tpl');
        $smarty->assign('content', implode('/', $this->PWE->getURL()->getMatchedAsArray()) . ':' . implode('/', $this->PWE->getURL()->getParamsAsArray()));
        $this->PWE->addContent($smarty);
    }

    public function getMenuLevel($level)
    {

    }

}

?>