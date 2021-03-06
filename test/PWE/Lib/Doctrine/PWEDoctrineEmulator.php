<?php

namespace PWE\Lib\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;

class PWEDoctrineEmulator implements Driver
{

    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        return new ConnectionEmulator();
    }

    public function getDatabase(Connection $conn)
    {

    }

    public function getDatabasePlatform()
    {
        return new PlatformEmulator();
    }

    public function getName()
    {

    }

    public function getSchemaManager(Connection $conn)
    {

    }

}
