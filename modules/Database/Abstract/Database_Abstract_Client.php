<?php

abstract class Database_Abstract_Client {
    /**
     * @var Database_Driver
     */
    protected $driver;

    public function __construct($dsnUrl = null) {
        $dsn = new Database_Dsn($dsnUrl);
        $driverClass = 'Database_Driver_' . ucfirst($dsn->scheme);
        $this->driver = new $driverClass($dsn);
    }

    /**
     * @param null $statement
     * @return Database_Query
     */
    public function query($statement = null) {
        $query = new Database_Query($statement, array(), $this->driver);
        return $query;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function quote($s) {
        return $this->driver->quote($s);
    }

    public function select() {
        // TODO implement
        //return new Database_Select($this);
    }

    public function delete() {
        // TODO implement
        //return new Databse_Delete($this);
    }

    public function insert() {
        // TODO implement
        //return new Database_Insert($this);
    }

    public function update() {
        // TODO implement
        //return new Database_Update($this);
    }
}