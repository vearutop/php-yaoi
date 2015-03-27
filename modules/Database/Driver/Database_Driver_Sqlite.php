<?php

class Database_Driver_Sqlite extends Database_Driver {

    /**
     * @var SQLite3
     */
    private $dbHandle;

    private function connect() {
        if (null === $this->dbHandle) {
            $this->dbHandle = new SQLite3($this->dsn->path);
        }
    }


    public function query($statement)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        return @$this->dbHandle->query($statement);
    }

    public function lastInsertId()
    {
        return $this->dbHandle->lastInsertRowID();
    }

    public function rowsAffected($result)
    {
        return $this->dbHandle->changes();
    }

    public function escape($value)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        return $this->dbHandle->escapeString($value);
    }

    /**
     * @param SQLite3Result $result
     */
    public function rewind($result)
    {
        $result->reset();
    }

    /**
     * @param SQLite3Result $result
     * @return array|false
     */
    public function fetchAssoc($result)
    {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row : null;
    }

    public function queryErrorMessage($result)
    {
        return $this->dbHandle->lastErrorCode() . ' ' . $this->dbHandle->lastErrorMsg();
    }

    public function disconnect()
    {
        if ($this->dbHandle) {
            $this->dbHandle->close();
        }
    }
}