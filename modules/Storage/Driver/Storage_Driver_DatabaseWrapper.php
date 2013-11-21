<?php

/**
 * Class Storage_Driver_DatabaseWrapper
 * TODO implement, WIP!
 */

class Storage_Driver_DatabaseWrapper extends Storage_Driver {

    private $table;
    private $clientId;

    /**
     * @return Database_Client
     */
    private function client() {
        if (null === $this->clientId) {
            $client = App::db($this->dsn->instanceId);
            $this->clientId = DependencyRepository::add($client);
        }
        return DependencyRepository::$items[$this->clientId];
    }


    function get($key)
    {
        return $this->client()->query("SELECT `val` FROM $this->table WHERE `key` = ?", $key)->fetchRow();
    }

    function keyExists($key)
    {
        return $this->client()->query("SELECT count(1) FROM $this->table WHERE `key` = ?", $key)->fetchRow();
    }

    function set($key, $value, $ttl)
    {
    }

    function delete($key)
    {
        $this->client()->query("DELETE FROM $this->table WHERE `key` = ?", $key);
    }

    function deleteAll()
    {
    }

} 