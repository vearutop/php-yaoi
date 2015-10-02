<?php
namespace YaoiTests\PHPUnit\Http\Client;

use Yaoi\Http\Client;
use Yaoi\Mock;
use Yaoi\Storage;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\TestUtil;

class ProxyTest extends TestCase
{

    public function testProxy()
    {
        $httpClient = new Client();
        $httpClient->followLocation = true;
        //$httpClient->logContext(new Log('stdout'));

        $storage = new Storage('serialized-file:///' . TestUtil::getResourcePath() . '/mocked-data-sets/HttpProxyTest.dat?compression=1&ignoreErrors=1');

        $mockSet = new Mock($storage);

        //$storage->deleteAll();$mockSet->mode = Mock::MODE_CAPTURE;
        $mockSet->mode = Mock::MODE_PLAY;

        $httpClient->mock($mockSet);


        $httpClient->setProxy('http://test:test@phph.tk:3129');
        $httpClient->url = 'http://tbex.ru/whoami';


        $response = $httpClient->fetch();
        $this->assertTrue((bool)strpos($response, '(phph.tk)'));
//        print_r($response);
    }

}