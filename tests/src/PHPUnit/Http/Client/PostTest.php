<?php

namespace YaoiTests\PHPUnit\Http\Client;


use Yaoi\Http\Client;
use Yaoi\String\Parser;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\PHPUnit\Http\Client\MirrorTestBase;
use YaoiTests\Helper\Http\ClientHelper;

class PostTest extends MirrorTestBase
{
    /**
     * Post data should be specified as array
     *
     * @see Yaoi\Http\Client::post
     */
    public function testSimple() {
        $client = new Client();
        $client->post = array(
            'login' => 'bgates',
            'password' => 'monkey',
            'form_token' => '123123123',
        );
        $response = $client->fetch('http://' . TestCase::$settings['envHttpMirrorServer'] . '/');
        $responseParsed = ClientHelper::parseRequest($response);
        $this->assertSame('login=bgates&password=monkey&form_token=123123123', $responseParsed['Body']);
    }


    /**
     * Post can have nested data
     *
     * @see Yaoi\Http\Client::post
     */
    public function testNestedData() {
        $client = new Client();
        $client->post = array(
            'form' => array(
                'login' => 'bgates',
                'password' => 'monkey'
            ),
            'form_token' => '123123123',
        );
        $response = $client->fetch('http://' . TestCase::$settings['envHttpMirrorServer'] . '/');
        $responseParsed = ClientHelper::parseRequest($response);
        $this->assertSame('form%5Blogin%5D=bgates&form%5Bpassword%5D=monkey&form_token=123123123', $responseParsed['Body']);
    }


    /**
     * If post contains objects, they will be converted to strings
     *
     * @see Yaoi\Http\Client::post
     */
    public function testStringObject() {
        $client = new Client();
        $client->post = array(
            'data' => Parser::create('<b>the-data</b>')->inner('<b>', '</b>'),
            'nestedData' =>
                array(
                    'one' => Parser::create('<i>the-data2</i>')->inner('<i>', '</i>'),
                ),
            'form_token' => '123123123',
        );
        $response = $client->fetch('http://' . TestCase::$settings['envHttpMirrorServer'] . '/');
        $responseParsed = ClientHelper::parseRequest($response);
        $this->assertSame('data=the-data&nestedData%5Bone%5D=the-data2&form_token=123123123', $responseParsed['Body']);
    }

}