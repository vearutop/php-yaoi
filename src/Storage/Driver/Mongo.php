<?php

namespace Yaoi\Storage\Driver;

use MongoBinData;
use MongoClient;
use MongoCollection;
use MongoDB;
use Yaoi\Date\TimeMachine;
use Yaoi\Storage\Contract\Driver;
use Yaoi\Storage\Settings;

class Mongo implements Driver
{
    /**
     * @var MongoClient
     */
    private $mongo;

    /**
     * @var MongoDB
     */
    private $db;

    /**
     * @var MongoCollection
     */
    private $collection;

    /**
     * @var Settings
     */
    private $dsn;

    public function __construct(Settings $dsn = null)
    {
        $this->dsn = $dsn;

        $host = $dsn->hostname ? $dsn->hostname : 'localhost';
        $port = $dsn->port ? $dsn->port : 27017;

        $dbCollection = 'storage';
        $dbName = 'test';
        if ($dsn->path) {
            $path = explode('/', $dsn->path, 2);
            $dbName = $path[0];
            if (isset($path[1])) {
                $dbCollection = $path[1];
            }
        }

        $server = 'mongodb://' . $host . ':' . $port;
        $this->mongo = new MongoClient($server);
        $this->db = $this->mongo->$dbName;
        $this->collection = $this->db->$dbCollection;
        $this->collection->ensureIndex(array('k' => 1));
    }

    public function get($key)
    {
        if ($res = $this->collection->findOne(array('k' => $key))) {
            if ($res['t'] && $res['t'] < TimeMachine::getInstance()->now()) {
                $this->delete($key);
                return null;
            }
            return $this->dsn->compression ? gzuncompress($res['v']->bin) : $res['v'];
        }
        return null;
    }

    public function keyExists($key)
    {
        if ($this->collection->findOne(array('k' => $key))) {
            return true;
        }
        return false;
    }

    public function set($key, $value, $ttl)
    {
        if (($this->dsn->compression || $this->dsn->binary) && !is_string($value)) {
            throw new \Yaoi\Storage\Exception('String data required for binary or compression',
                \Yaoi\Storage\Exception::SCALAR_REQUIRED);
        }

        if ($this->dsn->compression) {
            $v = gzcompress($value);
        } else {
            $v = $value;
        }

        if ($this->dsn->binary || $this->dsn->compression) {
            $v = new MongoBinData($v);
        }

        $this->collection->save(array('k' => $key, 'v' => $v, 't' => $ttl ? TimeMachine::getInstance()->now() + $ttl : null));
    }

    public function delete($key)
    {
        $this->collection->remove(array('k' => $key));
    }

    public function deleteAll()
    {
        $this->collection->remove(array());
    }

}