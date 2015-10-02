<?php
namespace YaoiTests\PHPUnit\Http\Client;

use Yaoi\Http\Client\UploadFile;
use Yaoi\Http\Client;
use Yaoi\Mock;
use Yaoi\Storage;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\TestUtil;

class UploadTest extends TestCase
{
    public function setUp()
    {
        if (empty(TestCase::$settings['envHttpMirrorServer'])) {
            $this->markTestSkipped('HttpMirror tests disabled');
            return;
        }
    }


    public function testClientUpload()
    {
        $storage = new Storage('serialized-file:///' . TestUtil::getMocksPath() . 'HttpUploadTest.serialized');

        $mockSet = new Mock($storage);
        //$storage->deleteAll(); $mockSet = new Mock_DataSetCapture($storage);

        \Yaoi\Date\TimeMachine::getInstance()->mock($mockSet);

        $client = Client::create();
        $mirror = TestCase::$settings['envHttpMirrorServer'];
        $client->url = 'http://' . $mirror . '/test/ooo.html/?dasd=34';

        $client->post = array(
            'first' => 'simple post',
            'uploaded_file' => UploadFile::createByContent('test file contents'),
            'upload2' => UploadFile::createByPath(TestUtil::getMocksPath() . 'dummy'),
            'foo' => 'bar',
        );


        $res = $client->fetch();

        $request = $mockSet->get('request', function () use ($res, $mirror) {
            return array(
                'mirror' => $mirror,
                'response' => $res,
            );
        });

        $expected = str_replace($request['mirror'], $mirror, $request['response']);

        $expectedParsed = \YaoiTests\Helper\Http\ClientHelper::parseRequest($expected);
        $resParsed = \YaoiTests\Helper\Http\ClientHelper::parseRequest($res);


        $this->assertArraySubset(
            $expectedParsed,
            $resParsed,
            false,
            print_r(array_diff($resParsed, $expectedParsed), 1)
        );
    }

    public function testPostDataToString()
    {
        $httpClient = new Client();

        $httpClient->post = array(
            'simple' => 'string',
            'object' => new \Yaoi\String\Parser('test-object')
        );

        $response = $httpClient->fetch('http://' . TestCase::$settings['envHttpMirrorServer']);
        if (false === strpos($response, 'simple=string&object=test-object')) {
            $this->assertTrue(false, 'Response is missing a substring');
        }

    }

}