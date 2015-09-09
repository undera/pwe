<?php

namespace PWE\Core;

use PWE\Modules\PWEModulesManager;
use PWE\Modules\TestPWEModulesManager;

class UnitTestPWECore extends PWECore
{

    public function setStructFile($param0)
    {
        $this->siteStructureFile = $param0;
        file_get_contents($this->siteStructureFile);
    }

    public function __construct()
    {
        parent::__construct();
        $tmp = \PWEUnitTests::utGetCleanTMP();

        $this->setDataDirectory($tmp);
        $this->setXMLDirectory($tmp);
        $this->setTempDirectory($tmp);
        $this->createModulesManager(new TestPWEModulesManager($this));

        $this->setStructFile(__DIR__ . "/../../dummyStruct.xml");
    }

    /**
     *
     * @return PWEModulesManager
     */
    public function getModulesManager()
    {
        return $this->modulesManager;
    }

    public function setURL($uri)
    {
        parent::setURL($uri);
    }
}
