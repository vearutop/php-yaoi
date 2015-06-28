<?php

namespace Yaoi\String;

use Yaoi\BaseClass;

class Dsn extends BaseClass
{
    public $scheme;
    public $username;
    public $password;
    public $hostname;
    public $port;
    public $path;

    public function __construct($dsnUrl = null)
    {
        if (null === $dsnUrl) {
            return;
        }

        if (false === ($pos = strpos($dsnUrl, '://'))) {
            $this->scheme = $dsnUrl;
            return;
        }

        if ('/' === $dsnUrl[$pos + 3]) {
            $dsnUrl = substr($dsnUrl, 0, $pos) . '://dummy' . substr($dsnUrl, $pos + 3);
        }

        if (!$p = parse_url($dsnUrl)) {
            throw new Exception('Malformed DSN URL', Exception::BAD_DSN);
        }

        if (isset($p['query'])) {
            parse_str($p['query'], $parsed);
            foreach ($parsed as $key => $value) {
                $this->$key = $value;
            }
        }

        if (isset($p['scheme'])) {
            $this->scheme = $p['scheme'];
        }

        if (isset($p['path'])) {
            $this->path = substr($p['path'], 1);
        }

        if (isset($p['user'])) {
            $this->username = $p['user'];
        }

        if (isset($p['pass'])) {
            $this->password = $p['pass'];
        }

        if (isset($p['host'])) {
            $this->hostname = $p['host'];
        }

        if (isset($p['port'])) {
            $this->port = $p['port'];
        }
    }


    public function __toString()
    {
        // http://user:password@host:port/path?query
        $result = $this->scheme;

        $h = $this->hostname;
        if ($h) {
            if ($this->port) {
                $h .= ':' . $this->port;
            }
            if ($this->username) {
                $h = '@' . $h;
                if ($this->password) {
                    $h = ':' . $this->password . $h;
                }
                $h = $this->username . $h;
            }
        }


        $result .= '://' . $h . '/' . $this->path;

        $params = (array)$this;
        unset($params['scheme']);
        unset($params['username']);
        unset($params['password']);
        unset($params['hostname']);
        unset($params['port']);
        unset($params['path']);

        if ($params) {
            $queryString = http_build_query($params);
            $result .= '?' . $queryString;
        }

        return $result;
    }

}