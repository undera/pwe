<?php

namespace PWE\Lib\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;
use PWE\Core\PWELogger;

class PWEDoctrineLogger implements SQLLogger
{

    private $count = 0;

    public function __destruct()
    {
        PWELogger::debug('Query count: %s', $this->count);
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->count++;
        PWELogger::debug("SQL #%s: %s\tParams: %s", $this->count, $sql, $params);
    }

    public function stopQuery()
    {
        PWELogger::debug('SQL #%s finished ', $this->count);
    }

}
