<?php

namespace PWE\Modules;

use PWE\Core\PWECore;

/**
 *
 * @author undera
 */
interface Setupable {

    public static function setup(PWECore $pwe, array &$registerData);
}

?>