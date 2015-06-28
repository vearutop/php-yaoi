<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Storage\Contract\Driver;
use Yaoi\Storage\Exception;
use Yaoi\BaseClass;
use Yaoi\Storage;
use Yaoi\Storage\Settings;

class SerializeProxy extends BaseClass implements Driver
{
    public function get($key)
    {
        $value = $this->storage->get($key);
        if ($value) {
            return unserialize($value);
        } else {
            return null;
        }
    }

    public function keyExists($key)
    {
        return $this->storage->keyExists($key);
    }

    public function set($key, $value, $ttl)
    {
        return $this->storage->set($key, serialize($value), $ttl);
    }

    public function delete($key)
    {
        return $this->storage->delete($key);
    }

    public function deleteAll()
    {
        return $this->storage->deleteAll();
    }

    /**
     * @var Storage
     */
    protected $storage;


    protected $settings;

    /**
     * @param Settings|null $dsn
     * @throws Exception
     * @throws \Yaoi\Service\Exception
     */
    public function __construct(Settings $dsn = null)
    {
        $this->settings = $dsn ? $dsn : Storage::createSettings();
        if (empty($dsn->proxyClient)) {
            throw new Exception('proxyClient required in dsn', Exception::PROXY_REQUIRED);
        }

        $this->storage = Storage::getInstance($this->settings->proxyClient);
    }

}