<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Storage\Contract\Expire;
use Yaoi\Date\TimeMachine;
use Yaoi\Storage\Settings;

class PhpVarExpire extends PhpVar implements Expire
{
    /**
     * @var TimeMachine
     */
    protected $time;

    /**
     * @var PhpVar
     */
    protected $expire;

    public function __construct(Settings $dsn = null)
    {
        parent::__construct($dsn);
        $this->expire = new PhpVar();
        $this->time = TimeMachine::getInstance($this->settings->dateSource);
    }

    public function set($key, $value, $ttl)
    {
        if ($ttl && $ttl < 30 * 86400) {
            $ttl = $this->time->now() + $ttl;
        }

        if ($ttl) {
            $this->expire->set($key, $ttl, 0);
        }

        parent::set($key, $value, $ttl);
    }

    public function get($key)
    {
        $value = parent::get($key);
        if (null !== $value) {
            $expire = $this->expire->get($key);
            if ($expire && $expire < $this->time->now()) {
                $this->delete($key);
                return null;
            }
            return $value;
        }

        return $value;
    }

    public function keyExists($key)
    {
        $this->get($key);
        return parent::keyExists($key);
    }
}