<?php

namespace PWE\Auth;

use PWE\Modules\PWEModule;
use PWE\Lib\Smarty\SmartyAssociative;
use PWE\Core\PWECore;
use PWE\Core\PWELogger;
use PWE\Exceptions\HTTP5xxException;

/**
 * Description of PWEUserAuthController
 *
 * @author undera
 */
abstract class PWEUserAuthController extends PWEModule implements SmartyAssociative {

    public static function getSmartyAllowedMethods() {
        return array('getUserID', 'getUserName', 'getLevelsUpToAuthNode');
    }

    abstract public function getUserID();

    abstract public function getUserName();

    abstract public function handleAuth();

    abstract public function handleLogout();

    /**
     * @param \PWE\Core\PWECore $pwe
     * @return PWEUserAuthController
     */
    public static function getAuthControllerInstance(PWECore $pwe) {
        try {
            $node = $pwe->getNode();
        } catch (HTTP5xxException $e) {
            PWELogger::warning("Failed to get pwe node in auth controller", $e);
        }

        if (!isset($node['!i']['authController']) || $node['!i']['authController'] == 'none')
            return new NoneAuthController($pwe);
        PWELogger::info('Page requires auth: ' . $node['!i']['authController']);
        return new $node['!i']['authController']($pwe);
    }

    public function getLevelsUpToAuthNode() {
        $levels = $this->PWE->getURL()->getParamsCount();
        $node = $this->PWE->getNode();
        while ($node && !isset($node['!a']['authController'])) {
            $node = &$node['!p'];
            $levels++;
        }
        return $levels;
    }

}

?>