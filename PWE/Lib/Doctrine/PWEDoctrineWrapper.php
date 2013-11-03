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

class PWEDoctrineWrapper extends PWEModule implements Setupable, PWECMDJob {

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

    public function __construct(PWECore $core) {
        parent::__construct($core);
        $this->DB = self::getConnection($core);
    }

    /**
     *
     * @param PWECore $PWE
     * @param bool $forceNewConnection
     * @return Connection
     */
    public static function getConnection(PWECore $PWE, $forceNewConnection = false) {
        if (!$forceNewConnection && self::$connection) {
            PWELogger::debug('Used cached connection');
            return self::$connection;
        }

        $settings = $PWE->getModulesManager()->getModuleSettings(self::getClass());
        $params = $settings['!c']['connection'][0]['!a'];

        $config = new Configuration();
        $config->setSQLLogger(new PWEDoctrineLogger());

        PWELogger::debug("Getting connection", $params);

        self::$connection = DriverManager::getConnection($params, $config);

        return self::$connection;
    }

    public static function setup(PWECore $pwe, array &$registerData) {
        if (!$registerData['!c']['connection']) {
            $db_config = array('driver' => 'pdo_mysql', 'user' => 'root',
                'dbname' => '[changeMe]', password => '[changeme]');
            $registerData['!c']['connection'][0]['!a'] = $db_config;
        }
    }

    public function processDBUpgrade($module, $filename_prefix = './') {
        PWELogger::info("Processing DB upgrade with module " . $module . " using prefix: " . $filename_prefix);
        if (!$filename_prefix)
            $filename_prefix = $module;

        // get current dbversion
        try {
            PWELogger::debug("Getting DB version number");
            $res = $this->DB->query("select dbversion from m_db_versions where module='" . ($module) . "'");
        } catch (DBALException $e) {
            PWELogger::debug("Creating DB versions table", $e);
            $this->DB->query("CREATE TABLE m_db_versions (
                  ID mediumint(8) NOT NULL auto_increment,
                  module varchar(255) NOT NULL,
                  dbversion mediumint(8) unsigned NOT NULL,
                  PRIMARY KEY  (ID),
                  UNIQUE KEY module (module)
                )");
            return $this->processDBUpgrade($module, $filename_prefix);
        }

        if ($res->rowCount()) {
            $version_row = $res->fetch();
            PWELogger::debug("DB version number", $version_row);
        } else {
            PWELogger::debug("Inserting first row for module: " . $module);
            $this->DB->query("insert into m_db_versions(module, dbversion) values('" . ($module) . "', 0)");
            $version_row = array('dbversion' => 0);
        }

        $version = $version_row['dbversion'];

        // попросим его прогнать файлы с запросами
        $version++;
        while (file_exists($filename_prefix . $version . '.sql') || file_exists($filename_prefix . $version . '.php')) {
            if (file_exists($filename_prefix . $version . '.sql')) {
                PWELogger::info("Executing file: " . $filename_prefix . $version . '.sql');
                $this->soakFile($filename_prefix . $version . '.sql');
            }
            if (file_exists($filename_prefix . $version . '.php')) {
                PWELogger::info("Executing file: " . $filename_prefix . $version . '.php');
                include_once $filename_prefix . $version . '.php';
            }
            $this->DB->query("update m_db_versions set dbversion=" . $version . " where module='" . $module . "'");
            $version++;
        }
        PWELogger::info("Done upgrading DB");

        return true;
    }

    public function soakFile($filename) {
        PWELogger::info('Processing SQL-file: ' . $filename);

        $sql = file_get_contents($filename);
        $splitter = strstr($sql, ";\r\n") ? ";\r\n" : ";\n";
        $sqls = explode($splitter, $sql);

        foreach ($sqls as $sql) {
            if (strlen(trim($sql))) {
                $this->DB->query($sql);
            }
        }
    }

    public function run() {
        // prevent from interrupting the long upgrade
        set_time_limit(0);
        $this->processDBUpgrade($args[0], $args[1]);
    }

    public static function getClass() {
        return __CLASS__;
    }

}

?>