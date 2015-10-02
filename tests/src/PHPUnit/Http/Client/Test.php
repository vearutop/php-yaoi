<?php
namespace YaoiTests\PHPUnit\Http\Client;

use Yaoi\Http\Client\Settings;
use Yaoi\Http\Client;
use Yaoi\Log;
use Yaoi\Mock;
use Yaoi\Storage;
use Yaoi\String\Parser;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\TestUtil;

class Test extends TestCase
{
    public function setUp()
    {
        if (empty(TestCase::$settings['envHttpPHPServer'])) {
            $this->markTestSkipped('HttpPHPServer disabled');
            return;
        }
    }


    private $httpClient;

    public function testUrlRender()
    {
        $h = Client::create();

        $h->url = 'https://www.google.com/ololo?query1';
        $this->assertSame('https://test.com/', $h->getAbsoluteUrl('//test.com/'));
        $this->assertSame('https://www.google.com/root/path?q', $h->getAbsoluteUrl('/root/path?q'));
        $this->assertSame('https://test.com/', $h->getAbsoluteUrl('https://test.com/'));
        $this->assertSame('http://test.com/', $h->getAbsoluteUrl('http://test.com/'));

        $this->assertSame('https://www.google.com/ololo?query2', $h->getAbsoluteUrl('?query2'));
        $this->assertSame('https://www.google.com/brbr?query2', $h->getAbsoluteUrl('brbr?query2'));
        $this->assertSame('https://www.google.com/path/', $h->getAbsoluteUrl('path/'));


        $h->url = "http://penix.tk/deep/throat";
        $this->assertSame('http://penix.tk/deep/path/', $h->getAbsoluteUrl('path/'));
    }


    public function testCookies()
    {
        $httpClient = new Client();

        $httpClient->url = 'http://' . TestCase::$settings['envHttpPHPServer'] . '/';
        $httpClient->followLocation = false;
        //$httpClient->logError(new Log('stdout'));
        //$httpClient->logRawResponseBody(new Log('stdout'));

        $this->assertSame("0", $httpClient->fetch());
        $this->assertSame("1", $httpClient->fetch());
        $this->assertSame("2", $httpClient->fetch());
        $this->assertSame("2", $httpClient->fetch());

        $httpClient->cookies = array();
        $this->assertSame("0", $httpClient->fetch());
        $this->assertSame("1", $httpClient->fetch());
        $this->assertSame("2", $httpClient->fetch());
        $this->assertSame("2", $httpClient->fetch());

    }

    public function testFollowLocation()
    {
        $httpClient = new Client();

        $httpClient->url = 'http://' . TestCase::$settings['envHttpPHPServer'] . '/';
        $httpClient->followLocation = true;
        $httpClient->logError(Log::create('stdout'));

        $this->assertSame("2", $httpClient->fetch());
        $this->assertSame("2", $httpClient->fetch());
    }


    public function testMirror()
    {
        if (empty(TestCase::$settings['envHttpMirrorServer'])) {
            echo 'HttpMirror tests disabled', "\n";
            return;
        }

        $this->httpClient = new Client();

        $this->httpClient->url = 'http://' . TestCase::$settings['envHttpMirrorServer'] . '/test/ooo.html/?dasd=34';
        $response = $this->httpClient->fetch();

        $expected = 'GET /test/ooo.html/?dasd=34 HTTP/1.0' . "\r\n"
            . 'Host: ' . TestCase::$settings['envHttpMirrorServer'] . "\r\n"
            . 'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0' . "\r\n"
            . 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . "\r\n"
            . 'Accept-Encoding: gzip, deflate' . "\r\n"
            . 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3' . "\r\n"
            . 'Connection: close' . "\r\n\r\n";

        $expectedParsed = \YaoiTests\Helper\Http\ClientHelper::parseRequest($expected);
        $responseParsed = \YaoiTests\Helper\Http\ClientHelper::parseRequest($response);

        $this->assertArraySubset($expectedParsed, $responseParsed);

        if ($expected !== $response) {
            $this->markTestIncomplete('Response data differs');
        }

        $form = array(
            'action' => '/',
            'method' => 'post',
            'data' =>
                array(
                    'lang' => 'Tcl',
                    'private' => 'True',
                    'run' => 'True',
                    'submit' => 'Submit',
                    'code' => '',
                ),
        );


        $this->httpClient->url = 'http://' . TestCase::$settings['envHttpMirrorServer'] . '/';
        $this->httpClient->post = $form['data'];
        $response = $this->httpClient->fetch();
        //var_dump($response);

        $expected = 'POST / HTTP/1.0' . "\r\n"
            . 'Host: ' . TestCase::$settings['envHttpMirrorServer'] . "\r\n"
            . 'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64; rv:21.0) Gecko/20100101 Firefox/21.0' . "\r\n"
            . 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' . "\r\n"
            . 'Accept-Encoding: gzip, deflate' . "\r\n"
            . 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3' . "\r\n"
            . 'Connection: close' . "\r\n"
            . 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8' . "\r\n"
            . 'Content-Length: 50' . "\r\n"
            . 'Referer: http://' . TestCase::$settings['envHttpMirrorServer'] . '/test/ooo.html/?dasd=34' . "\r\n\r\n"
            . 'lang=Tcl&private=True&run=True&submit=Submit&code=';
        $expectedParsed = \YaoiTests\Helper\Http\ClientHelper::parseRequest($expected);
        $responseParsed = \YaoiTests\Helper\Http\ClientHelper::parseRequest($response);

        $this->assertArraySubset($expectedParsed, $responseParsed);

        // TODO wtf!
        // ABNORMAL \r\n\r\n with file_get_contents at debian 6 php 5.3.3
        /*
         * echo '<pre>';
$form = array('one'=>1);
$content = http_build_query($form);
        $context = array(
            'http' => array(
                'method' => 'POST',
                'header' => '',
            ),
        );
$context['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
$context['http']['header'] .= "Content-Length: ".strlen($content)."\r\n";
$context['http']['content'] = $content;
$ctx = stream_context_create($context);
var_dump(file_get_contents('http://corehard.ru:1337', false, $ctx));

        string(120) "POST / HTTP/1.0
Host: corehard.ru:1337
Content-Type: application/x-www-form-urlencoded
Content-Length: 5

one=1

"
         */

    }


    public function testCompressed()
    {
        $httpClient = new Client();
        $httpClient->followLocation = true;
        //$httpClient->logContext(new Log('stdout'));

        $storage = new Storage('serialized-file:///' . TestUtil::getMocksPath() . 'HttpCompressedTest-testGet.dat?compression=1&ignoreErrors=1');

        $mockSet = new Mock($storage);

        //$storage->deleteAll();$mockSet->mode = Mock::MODE_CAPTURE;
        $mockSet->mode = Mock::MODE_PLAY;

        $httpClient->mock($mockSet);

        //$url = 'http://ya.ru/';
        $url = 'http://yandex.ru/search/?lr=10553&ymsid=20937.26619.1430734657.80043&text=ololo!';
        //$url = 'https://www.google.com/search?q=accept-encoding+deflate+server&ie=utf-8&oe=utf-8';

        $httpClient->url = $url;
        unset($httpClient->headers['Accept-Encoding']);
        $responsePlain = $httpClient->fetch();
        $this->assertTrue(Parser::create($responsePlain)->starts('<!DOCTYPE html>'));

        //echo $responsePlain;

        $httpClient->url = $url;
        $httpClient->headers['Accept-Encoding'] = 'gzip';
        $responseGzip = $httpClient->fetch();
        $this->assertTrue(Parser::create($responseGzip)->starts('<!DOCTYPE html>'));


        // no deflate content-encoding in real world, todo deprecate support
        /*
        $httpClient->url = $url;
        $httpClient->headers['Accept-Encoding'] = 'deflate';
        $responseDeflate = $httpClient->fetch();
        var_dump($httpClient->responseHeaders);
        //$this->assertSame($responsePlain, $responseDeflate);
        */
    }


    /**
     * @expectedException \Yaoi\Http\Client\Exception
     * @expectedExceptionCode \Yaoi\Http\Client\Exception::EMPTY_URL
     */
    public function testEmptyUrl()
    {
        $httpClient = new Client();
        $httpClient->fetch();
    }


    public function testDsn()
    {
        $httpClient = Client::getInstance(function () {
            $dsn = new Settings();
            $dsn->log = new Log('void');
            $dsn->proxy = 'http://test:test@phph.tk:3129';
            return $dsn;
        });
        $httpClient->followLocation = true;
        //$httpClient->logContext(new Log('stdout'));

        $storage = new Storage('serialized-file:///' . TestUtil::getMocksPath() . '/HttpProxyTest.dat?compression=1&ignoreErrors=1');

        $mockSet = new Mock($storage);

        //$storage->deleteAll();$mockSet->mode = Mock::MODE_CAPTURE;
        $mockSet->mode = Mock::MODE_PLAY;

        $httpClient->mock($mockSet);

        $httpClient->url = 'http://tbex.ru/whoami';


        $response = $httpClient->fetch();
        $this->assertTrue((bool)strpos($response, '(phph.tk)'));
//        print_r($response);
    }


}