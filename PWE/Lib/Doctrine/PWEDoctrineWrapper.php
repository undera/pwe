<?php

namespace PWE\Lib\Doctrine;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use PWE\Core\PWECMDJob;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Modules\PWEModule;
use PWE\Modules\Setupable;

class PWEDoctrineWrapper extends PWEModule implements Setupable, PWECMDJob
{

    /**
     *
     * @var Connection
     */
    private static $connection;

    /**
     *
     * @var Connection
     */
    private $DB;

    public function __construct(PWECore $core)
    {
        parent::__construct($core);
        $this->DB = self::getConnection($core);
    }

    /**
     *
     * @param PWECore $PWE
     * @param bool $forceNewConnection
     * @return Connection
     */
    public static function getConnection(PWECore $PWE, $forceNewConnection = false)
    {
        if (!$forceNewConnection && self::$connection) {
            PWELogger::debug('Used cached connection');
            return self::$connection;
        }

        $settings = $PWE->getModulesManager()->getModuleSettings(self::getClass());
        $params = $settings['!c']['connection'][0]['!a'];

        $config = new Configuration();
        $config->setSQLLogger(new PWEDoctrineLogger());

        PWELogger::debug("Getting connection: %s", $params);

        self::$connection = DriverManager::getConnection($params, $config);

        return self::$connection;
    }

    public static function setup(PWECore $pwe, array &$registerData)
    {
        if (!$registerData['!c']['connection']) {
            $db_config = array('driver' => 'pdo_mysql', 'user' => 'root',
                'dbname' => '[changeMe]', 'password' => '[changeme]');
            $registerData['!c']['connection'][0]['!a'] = $db_config;
        }
    }

    public function processDBUpgrade($module, $filename_prefix = './')
    {
        PWELogger::info("Processing DB upgrade with module %s using prefix: %s", $module, $filename_prefix);
        if (!$filename_prefix)
            $filename_prefix = $module;

        // get current dbversion
        try {
            PWELogger::debug("Getting DB version number");
            $res = $this->DB->query("select dbversion from m_db_versions where module='" . ($module) . "'");
        } catch (DBALException $e) {
            PWELogger::debug("Creating DB versions table: %s", $e);
            $this->DB->query("CREATE TABLE m_db_versions (
                  ID MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
                  module VARCHAR(255) NOT NULL,
                  dbversion MEDIUMINT(8) UNSIGNED NOT NULL,
                  PRIMARY KEY  (ID),
                  UNIQUE KEY module (module)
                )");
            return $this->processDBUpgrade($module, $filename_prefix);
        }

        if ($res->rowCount()) {
            $version_row = $res->fetch();
            PWELogger::debug("DB version number: %s", $version_row);
        } else {
            PWELogger::debug("Inserting first row for module: %s", $module);
            $this->DB->query("insert into m_db_versions(module, dbversion) values('" . ($module) . "', 0)");
            $version_row = array('dbversion' => 0);
        }

        $version = $version_row['dbversion'];

        // попросим его прогнать файлы с запросами
        $version++;
        while (file_exists($filename_prefix . $version . '.sql') || file_exists($filename_prefix . $version . '.php')) {
            if (file_exists($filename_prefix . $version . '.sql')) {
                PWELogger::info("Executing file: %s%s.sql", $filename_prefix, $version);
                $this->soakFile($filename_prefix . $version . '.sql');
            }
            $fname = $filename_prefix . $version . '.php';
            if (file_exists($fname)) {
                PWELogger::info("Executing file: %s", $fname);
                require $fname;
            }
            $this->DB->executeQuery("update m_db_versions set dbversion=? where module=?", array($version, $module));
            $version++;
        }
        PWELogger::info("Done upgrading DB");

        return true;
    }

    public function soakFile($filename)
    {
        PWELogger::info('Processing SQL-file: %s', $filename);

        $sql = file_get_contents($filename);
        $splitter = strstr($sql, ";\r\n") ? ";\r\n" : ";\n";
        $sqls = explode($splitter, $sql);

        foreach ($sqls as $sql) {
            if (strlen(trim($sql))) {
                $this->DB->query($sql);
            }
        }
    }

    public function run()
    {
        // prevent from interrupting the long upgrade
        set_time_limit(0);

        // have to add j:c:r: from index.php
        $opts = getopt("j:c:r:m:p:");
        PWELogger::debug("Opts", $opts);
        if (!$opts["m"] || !$opts["p"]) {
            throw new \InvalidArgumentException("Both -m and -p options required");
        }
        $this->processDBUpgrade($opts["m"], $opts["p"]);
    }

    public static function getClass()
    {
        return __CLASS__;
    }

}
