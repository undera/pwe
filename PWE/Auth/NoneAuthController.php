<?php

namespace PWE\Auth;

use PWE\Core\PWELogger;

/**
 * Default auth controller - no control
 *
 * @author undera
 */
final class NoneAuthController extends PWEUserAuthController
{

    public static function getClassName()
    {
        return __CLASS__;
    }

    public function getUserID()
    {
        return false;
    }

    public function handleAuth()
    {
        PWELogger::debug("None auth");
    }

    public function getUserName()
    {
        return "No authentication required";
    }

    public function handleLogout()
    {
        PWELogger::debug("None logout");
    }

}

