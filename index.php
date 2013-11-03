<?php

/**
 *  PWE Framework entry file
 * @author Andrey Pohilko <apc@apc.kg>
 * @version 1.5
 */

namespace PWE;

//phpinfo(INFO_VARIABLES);
use InvalidArgumentException;
use PWE\Core\CMDLinePWECore;
use PWE\Core\PWEAutoloader;
use PWE\Core\PWECMDJob;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\PHPFatalException;

require_once dirname(__FILE__) . '/PWE/Lib/Smarty/SmartyAssociative.php';
require_once dirname(__FILE__) . '/PWE/Modules/Setupable.php';
require_once dirname(__FILE__) . '/PWE/Modules/Outputable.php';
require_once dirname(__FILE__) . '/PWE/Core/PWECMDJob.php';

require_once dirname(__FILE__) . '/PWE/Core/PWEURL.php';
require_once dirname(__FILE__) . '/PWE/Modules/PWEModulesManager.php';
require_once dirname(__FILE__) . '/PWE/Utils/PWEXMLFunctions.php';
require_once dirname(__FILE__) . '/PWE/Utils/PWEXML.php';
require_once dirname(__FILE__) . '/PWE/Modules/PWEModule.php';
require_once dirname(__FILE__) . '/PWE/Auth/PWEUserAuthController.php';
require_once dirname(__FILE__) . '/PWE/Exceptions/PHPFatalException.php';

require_once dirname(__FILE__) . '/PWE/Core/AbstractPWECore.php';
require_once dirname(__FILE__) . '/PWE/Core/PWECore.php';

require_once dirname(__FILE__) . '/PWE/Core/PWELogger.php';
require_once dirname(__FILE__) . '/PWE/Core/PWEAutoloader.php';

PWEAutoloader::activate();
PHPFatalException::activate();

if (php_sapi_name() == 'cli') {
    $shortopts = "j:";
    $shortopts .= "c:";
    $shortopts .= "r:";

    $opts = getopt($shortopts);


    $registry = $opts['r'] ? $opts['r'] : '/etc/loadosophia/eg_globals.xml';
    $pwe = new CMDLinePWECore($registry);
    if ($opts['c']) {
        $PWECore = $pwe;
        require $opts['c'];
    }
    PWEAutoloader::setPWE($pwe);

    if (!$opts['j']) {
        throw new InvalidArgumentException("-j option with full job class name required");
    }

    $job = new $opts['j']($pwe);
    if (!($job instanceof PWECMDJob)) {
        throw new InvalidArgumentException("Job class must implement PWECMDJob");
    }
    $job->run();
} else {
    try {
        $PWECore = new PWECore();
        PWEAutoloader::setPWE($PWECore);

        require_once dirname($_SERVER['SCRIPT_FILENAME']) . '/cfg.php';

        echo $PWECore->process($_SERVER['REDIRECT_URL'] ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI']);
        PWELogger::info("PWE Done.");
    } catch (\Exception $e) {
        try {
            if ($e->getCode() != 404) {
                PWELogger::error('Exception occured at page: ' . $_SERVER['REDIRECT_URL'], $e);
            }
            header($_SERVER["SERVER_PROTOCOL"] . ' ' . $e->getCode());
            header("Content-Type: text/html");
            echo $PWECore->getErrorPage($e);
        } catch (\Exception $e2) {
            echo "<textarea cols='100' rows='25' readonly='readonly'>";
            echo $e2->__toString();
            echo "</textarea><br>";
            echo "<p>Caused by:</p>";
            echo "<textarea cols='100' rows='25' readonly='readonly'>";
            echo $e->__toString();
            echo "</textarea><br>";
            die($e2->getMessage());
        }
    }
}
?>