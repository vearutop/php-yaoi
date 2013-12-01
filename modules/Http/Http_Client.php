<?php

/**
 * Class Http_Client
 * TODO detect response charset
 */
class Http_Client {
    const LOG_URL = 1;
    const LOG_POST = 2;
    const LOG_CONTEXT = 4;
    const LOG_RESPONSE_HEADERS = 8;
    const LOG_RESPONSE_BODY = 16;

    const FILENAME_LOG = 'http_client.log';

    public $logFlags = 0;
    public $logOnce;

    /**
     * @var Log
     */
    public $logName = self::FILENAME_LOG;



    public $cookies = array();
    public $charset = 'UTF-8';
    public $post;
    public $url;
    public $referrer;
    public $followLocation = false;
    public $responseHeaders = array();
    public $parsedHeaders = array();
    public $headers = array();
    public $defaultHeaders = array(
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0',
    );

    public function reset() {
        $this->headers = $this->defaultHeaders;
        $this->post = null;
        $this->url = null;

        return $this;
    }

    public function parseResponseCookies() {
        $cookies = array();
        $this->parsedHeaders = array();
        foreach ($this->responseHeaders as $hdr) {
            if ($p = strpos($hdr, ':')) {
                $header = substr($hdr, 0, $p);
                $value = trim(substr($hdr, $p + 1));

                $tmp = explode(';', $value);
                $valueParams = array(
                    'value' => $value,
                );
                if (count($tmp) > 1) {
                    foreach ($tmp as $tm) {
                        $tm = explode('=', trim($tm), 2);
                        if (isset($tm[1])) {
                            $valueParams[$tm[0]] = $tm[1];
                        }
                        else {
                            $valueParams['baseValue'] = $tm[0];
                        }
                    }
                }

                $this->parsedHeaders [$header]= $valueParams;
            }

            if (preg_match('/^Set-Cookie:\s*([^;]+)/', $hdr, $matches)) {
                //echo $hdr;
                parse_str($matches[1], $tmp);
                $cookies += $tmp;
            }

            //Content-Type: text/html; charset=WINDOWS-1251
        }

        if (isset($this->parsedHeaders['Content-Type']['charset'])) {
            $this->charset = $this->parsedHeaders['Content-Type']['charset'];
        }

        $this->cookies = array_merge($this->cookies, $cookies);
    }

    public function fetch() {
        $logFlags = $this->logFlags;
        if ($this->logOnce) {
            $logFlags = $this->logOnce;
            $this->logOnce = null;
        }

        $context = array(
            'http' => array(
                'method' => 'GET',
                'follow_location' => false, //$this->followLocation, // don't or do follow redirects
                'header' => '',
            ),
        );
        $headers = $this->headers;
        if ($this->cookies) {
            $headers['Cookie'] = http_build_query($this->cookies, null, '; ');
        }

        if ($this->post) {
            $context['http']['method'] = 'POST';
            $context['http']['content'] = http_build_query($this->post);
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $headers['Content-Length'] = strlen($context['http']['content']);
        }

        if ($this->referrer) {
            $headers['Referer'] = $this->referrer;
        }
        $this->referrer = $this->url;

        foreach ($headers as $type => $value) {
            $context['http']['header'] .= $type . ': ' . $value . "\r\n";
        }


        $log = '';
        if ($logFlags) {
            $log .= ''
                . ($logFlags & self::LOG_URL ? print_r($this->url, 1) . "\n" : '')
                . ($logFlags & self::LOG_POST ? print_r($this->post, 1) : '')
                . ($logFlags & self::LOG_CONTEXT ? print_r($context, 1) : '')
                ;

        }

        $this->responseHeaders = array();

        if ($this->mock) {
            $response = '';
            $mock = $this->mock->branch(crc32(serialize($context)), $this->url);
            if ($mock instanceof Mock_DataSetPlay) {
                $response = $mock->get(null, 'response');
                $this->responseHeaders = $mock->get(null, 'responseHeaders');
            }
            elseif ($mock instanceof Mock_DataSetCapture) {
                $ctx = stream_context_create($context);
                $response = @file_get_contents($this->url, false, $ctx);
                foreach ($http_response_header as $hdr) {
                    $this->responseHeaders []= $hdr;
                }
                if (false === $response) {
                    $e = new Http_ClientException('Bad request', Http_ClientException::BAD_REQUEST);
                    $e->context = $context;
                    $e->responseHeaders = $this->responseHeaders;
                    $e->url = $this->url;
                    throw $e;
                }

                $mock->add(null, $response, 'response');
                $mock->add(null, $this->responseHeaders, 'responseHeaders');
            }
        }
        else {
            $ctx = stream_context_create($context);
            $response = @file_get_contents($this->url, false, $ctx);
            foreach ($http_response_header as $hdr) {
                $this->responseHeaders []= $hdr;
            }
            if (false === $response) {
                $e = new Http_ClientException('Bad request', Http_ClientException::BAD_REQUEST);
                $e->context = $context;
                $e->responseHeaders = $this->responseHeaders;
                $e->url = $this->url;
                throw $e;
            }
        }


        if ($logFlags) {
            $log .= ''
                . ($logFlags & self::LOG_RESPONSE_HEADERS ? print_r($this->responseHeaders, 1) : '')
                . ($logFlags & self::LOG_RESPONSE_BODY ? print_r($response, 1) : '')
                ;
            Log::get($this->logName)->write($log);
        }

        //print_r($this->responseHeaders);
        $this->parseResponseCookies();
        //print_r($response);

        return $response;
    }


    /**
     * @var Mock_DataSetBase
     */
    private $mock;
    public function mock(Mock_DataSet $dataSet = null) {
        $this->mock = $dataSet;
    }


}
