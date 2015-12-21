<?php

namespace PWE\Lib\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;
use PWE\Core\PWELogger;

class PWEDoctrineLogger implements SQLLogger
{

    private $count = 0;
    private $prefix = "";

    public function __construct($alias = '')
    {
        $this->prefix = $alias;
    }

    public function __destruct()
    {
        PWELogger::debug('Query count %s: %s', $this->prefix, $this->count);
    }

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->count++;
        PWELogger::debug("%s #%s: %s\tParams: %s", $this->prefix, $this->count, $sql, $params);
    }

    public function stopQuery()
    {
        PWELogger::debug('%s #%s finished ', $this->prefix, $this->count);
    }

}
