<?php

namespace PWE\Core;

use PWE\Modules\UnitTestModulesManager;
use PWE\Utils\FilesystemHelper;

/**
 * Description of UnitTestPWECore
 *
 * @author undera
 */
class UnitTestPWECore extends PWECore {

    public function setStructFile($param0) {
        $this->siteStructureFile = $param0;
    }

    public function __construct() {
        parent::__construct();
        $tmp = tempnam("/tmp", "pwe-");
        unlink($tmp);
        FilesystemHelper::fsys_mkdir($tmp);

        $this->setDataDirectory($tmp);
        $this->setXMLDirectory($tmp);
        $this->setTempDirectory($tmp);
        $this->createModulesManager(new \PWE\Modules\TestPWEModulesManager($this));

        $this->setStructFile(dirname(__FILE__) . "/../../dummyStruct.xml");
    }

    /**
     *
     * @return PWEModulesManager
     */
    public function getModulesManager() {
        return $this->modulesManager;
    }

}

?>
