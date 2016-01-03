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
use PWE\Utils\PWEXMLFunctions;

class PWEDoctrineWrapper extends PWEModule implements Setupable, PWECMDJob
{

    /**
     *
     * @var Connection
     */
    private static $connection = array();

    /**
     *
     * @param PWECore $PWE
     * @param bool $forceNewConnection
     * @param string $alias
     * @return Connection
     * @throws DBALException
     */
    public static function getConnection(PWECore $PWE, $forceNewConnection = false, $alias = null)
    {
        if (!$forceNewConnection && self::$connection[$alias]) {
            PWELogger::debug('Used cached connection');
            return self::$connection[$alias];
        }

        $settings = $PWE->getModulesManager()->getModuleSettings(self::getClass());
        $connections = $settings['!c']['connection'];
        $ix = PWEXMLFunctions::findNodeWithAttributeValue($connections, 'alias', $alias);
        if ($ix < 0) {
            throw new \InvalidArgumentException("Alias $alias not found in database configs");
        }

        $params = $connections[$ix]['!a'];

        $config = new Configuration();
        $config->setSQLLogger(new PWEDoctrineLogger($alias ? $alias : ''));

        PWELogger::debug("Getting connection: %s", $params);

        self::$connection[$alias] = DriverManager::getConnection($params, $config);

        return self::$connection[$alias];
    }

    public static function setup(PWECore $pwe, array &$registerData)
    {
        if (!$registerData['!c']['connection']) {
            $db_config = array('driver' => 'pdo_mysql', 'user' => 'root',
                'dbname' => '[changeMe]', 'password' => '[changeme]');
            $registerData['!c']['connection'][0]['!a'] = $db_config;
        }
    }

    public function processDBUpgrade(Connection $DB, $module, $filename_prefix = './')
    {
        PWELogger::info("Processing DB upgrade with module %s using prefix: %s", $module, $filename_prefix);
        if (!$filename_prefix)
            $filename_prefix = $module;

        // get current dbversion
        try {
            PWELogger::debug("Getting DB version number");
            $res = $DB->query("select dbversion from m_db_versions where module='" . ($module) . "'");
        } catch (DBALException $e) {
            PWELogger::debug("Creating DB versions table: %s", $e);
            $DB->query("CREATE TABLE m_db_versions (
                  ID MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
                  module VARCHAR(255) NOT NULL,
                  dbversion MEDIUMINT(8) UNSIGNED NOT NULL,
                  PRIMARY KEY  (ID),
                  UNIQUE KEY module (module)
                )");
            return $this->processDBUpgrade($DB, $module, $filename_prefix);
        }

        if ($res->rowCount()) {
            $version_row = $res->fetch();
            PWELogger::debug("DB version number: %s", $version_row);
        } else {
            PWELogger::debug("Inserting first row for module: %s", $module);
            $DB->query("insert into m_db_versions(module, dbversion) values('" . ($module) . "', 0)");
            $version_row = array('dbversion' => 0);
        }

        $version = $version_row['dbversion'];

        // попросим его прогнать файлы с запросами
        $version++;
        while (file_exists($filename_prefix . $version . '.sql') || file_exists($filename_prefix . $version . '.php')) {
            if (file_exists($filename_prefix . $version . '.sql')) {
                PWELogger::info("Executing file: %s%s.sql", $filename_prefix, $version);
                $this->soakFile($DB, $filename_prefix . $version . '.sql');
            }
            $fname = $filename_prefix . $version . '.php';
            if (file_exists($fname)) {
                PWELogger::info("Executing file: %s", $fname);
                require $fname;
            }
            $DB->executeQuery("update m_db_versions set dbversion=? where module=?", array($version, $module));
            $version++;
        }
        PWELogger::info("Done upgrading DB");

        return true;
    }

    public function soakFile(Connection $DB, $filename)
    {
        PWELogger::info('Processing SQL-file: %s', $filename);

        $sql = file_get_contents($filename);
        $splitter = strstr($sql, ";\r\n") ? ";\r\n" : ";\n";
        $sqls = explode($splitter, $sql);

        foreach ($sqls as $sql) {
            if (strlen(trim($sql))) {
                $DB->query($sql);
            }
        }
    }

    public function run()
    {
        // prevent from interrupting the long upgrade
        set_time_limit(0);

        // have to add j:c:r: from index.php
        $opts = $this->getOpts();
        PWELogger::debug("Opts", $opts);
        if (!$opts["m"] || !$opts["p"]) {
            throw new \InvalidArgumentException("Both -m and -p options required");
        }

        $DB = self::getConnection($this->PWE, true, $opts["a"]);

        $this->processDBUpgrade($DB, $opts["m"], $opts["p"]);
    }

    public static function getClass()
    {
        return __CLASS__;
    }

    protected function getOpts()
    {
        return getopt("j:c:r:m:p:a:");
    }

}
