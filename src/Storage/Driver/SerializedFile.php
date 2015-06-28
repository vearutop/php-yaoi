<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Storage\Exception;
use Yaoi\Storage\Settings;

class SerializedFile extends PhpVar
{

    /**
     * @var Settings
     */
    protected $settings;

    protected $fileName;
    protected $loaded;

    protected function load()
    {
        $this->fileName = $this->settings->path;

        if (file_exists($this->fileName)) {
            if ($this->settings->compression) {
                //$this->data = @unserialize(file_get_contents($this->fileName));
                $this->data = @unserialize(gzuncompress(file_get_contents($this->fileName)));
            } else {
                $this->data = @unserialize(file_get_contents($this->fileName));
            }
            if (false === $this->data) {
                if (empty($this->settings->ignoreErrors)) {
                    throw new Exception('Bad serialized data', Exception::BAD_SERIALIZED_DATA);
                } else {
                    $this->data = array();
                }
            }
        } else {
            $this->data = array();
        }
        $this->loaded = true;
    }

    public function set($key, $value, $ttl)
    {
        if (!$this->loaded) {
            $this->load();
        }
        parent::set($key, $value, $ttl);
    }

    public function get($key)
    {
        if (!$this->loaded) {
            $this->load();
        }
        return parent::get($key);
    }

    public function delete($key)
    {
        if (!$this->loaded) {
            $this->load();
        }
        parent::delete($key);
    }


    function deleteAll()
    {
        if (!$this->loaded) {
            $this->load();
        }
        parent::deleteAll();
        $this->saveAll();
    }


    protected function saveAll()
    {
        if (!$this->modified) {
            return;
        }
        if ($this->settings->compression) {
            file_put_contents($this->fileName, gzcompress(serialize($this->data)));
        } else {
            file_put_contents($this->fileName, serialize($this->data));
        }
        $this->modified = false;
    }

    public function __destruct()
    {
        $this->saveAll();
    }


    public function &exportArray()
    {
        if (!$this->loaded) {
            $this->load();
        }
        return $this->data;
    }

    public function importArray(array &$data)
    {
        if (!$this->loaded) {
            $this->load();
        }
        $this->data = $data;
        $this->modified = true;
    }

}