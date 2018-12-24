<?php

namespace PWE\Lib\Doctrine;

use Doctrine\DBAL\Driver\Statement;
use PDO;

class StatementEmulator implements \IteratorAggregate, Statement
{

    public function bindValue($param, $value, $type = null)
    {

    }

    public function closeCursor()
    {

    }

    public function columnCount()
    {

    }

    public function errorCode()
    {

    }

    public function errorInfo()
    {

    }

    public function execute($params = null)
    {

    }

    public function fetch($fetchMode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {

    }

    public function fetchAll($fetchMode = null, $fetchArgument = null, $ctorArgs = null)
    {

    }

    public function fetchColumn($columnIndex = 0)
    {

    }

    public function rowCount()
    {

    }

    public function current()
    {

    }

    public function key()
    {

    }

    public function next()
    {

    }

    public function rewind()
    {

    }

    public function valid()
    {

    }

    public function getIterator()
    {

    }

    public function bindParam($column, &$variable, $type = null, $length = null)
    {

    }

    public function setFetchMode($fetchMode, $arg2 = null, $arg3 = null)
    {

    }

}
