<?php

namespace PWE\Lib\Doctrine;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;

class ConnectionEmulator implements Connection {

    public function beginTransaction() {
        
    }

    public function commit() {
        
    }

    public function errorCode() {
        
    }

    public function errorInfo() {
        
    }

    public function exec($statement) {
        
    }

    public function lastInsertId($name = null) {
        
    }

    public function prepare($prepareString) {
        
    }

    public function query() {
        return new StatementEmulator();
    }

    public function quote($input, $type = \PDO::PARAM_STR) {
        
    }

    public function rollBack() {
        
    }

}

?>