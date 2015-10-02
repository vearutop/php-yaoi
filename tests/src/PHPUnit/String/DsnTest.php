<?php
namespace YaoiTests\PHPUnit\String;

use Yaoi\String\Dsn;
use Yaoi\String\Exception;
use Yaoi\Test\PHPUnit\TestCase;


class DsnTest extends TestCase
{

    /**
     * URL-like dsn scheme
     * @see \Yaoi\String\Dsn
     */
    public function testUrlScheme()
    {
        $this->assertSame(
            array(
                'scheme' => 'scheme',
                'username' => 'user',
                'password' => 'pass',
                'hostname' => 'host',
                'port' => 1234,
                'path' => 'path/deeper/',
                'param1' => '1',
                'param2' => '2',
            ),
            get_object_vars(new Dsn('scheme://user:pass@host:1234/path/deeper/?param1=1&param2=2'))
        );
    }


    /**
     * User credential scheme (username and password separated by colon)
     * @see \Yaoi\String\Dsn
     */
    public function testCredentialsScheme()
    {
        $this->assertSame(
            array(
                'scheme' => NULL,
                'username' => 'user',
                'password' => 'password',
                'hostname' => NULL,
                'port' => NULL,
                'path' => NULL,
            ),
            get_object_vars(new Dsn('user:password'))
        );
    }


    /**
     * Scheme-only (string without "://")
     * @see \Yaoi\String\Dsn
     */
    public function testSchemeOnly()
    {
        $this->assertSame(
            array(
                'scheme' => 'scheme',
                'username' => NULL,
                'password' => NULL,
                'hostname' => NULL,
                'port' => NULL,
                'path' => NULL,
            ),
            get_object_vars(new Dsn('scheme'))
        );
    }

    /**
     * Special characters (@, :, /, ?, &) in values should be prepended with \
     * It is not required to escape space symbol
     * @see \Yaoi\String\Dsn
     */
    public function testEscape()
    {
        /**
         * URL scheme
         */
        $this->assertSame(
            array(
                'scheme' => 'scheme',
                'username' => 'john.doe@mail.com',
                'password' => 'p@ssw:rd',
                'hostname' => 'host',
                'port' => NULL,
                'path' => NULL,
                'company' => 'Smith & Wesson',
            ),
            get_object_vars(new Dsn('scheme://john.doe\@mail.com:p\@ssw\:rd@host?company=Smith \& Wesson'))
        );

        /**
         * Credentials scheme
         */
        $this->assertSame(
            array(
                'scheme' => NULL,
                'username' => 'john.doe@mail.com',
                'password' => 'p@ssw:rd',
                'hostname' => NULL,
                'port' => NULL,
                'path' => NULL,
            ),
            get_object_vars(new Dsn('john.doe\@mail.com:p\@ssw\:rd'))
        );


        /**
         * Scheme-only
         */
        $this->assertStringEqualsSpaceless(
            array(
                'scheme' => 'sch:eme',
                'username' => NULL,
                'password' => NULL,
                'hostname' => NULL,
                'port' => NULL,
                'path' => NULL,
            ),
            get_object_vars(new Dsn('sch\:eme'))
        );

    }


    public function testDsn()
    {
        $dsn = new Dsn('http-dfdf://user:password@host.dsd:801/path/to/?param1=43&param2&param3=5');

        $this->assertSame($dsn->scheme, 'http-dfdf');
        $this->assertSame($dsn->hostname, 'host.dsd');
        $this->assertSame($dsn->password, 'password');
        $this->assertSame($dsn->username, 'user');
        $this->assertSame($dsn->port, 801);
        $this->assertSame($dsn->path, 'path/to/');
        $this->assertSame($dsn->param1, '43');
        $this->assertSame($dsn->param2, '');
        $this->assertSame($dsn->param3, '5');

        $dsn = new Dsn('proto://host//trailing/slash/path');
        $this->assertSame('/trailing/slash/path', $dsn->path);
    }


    public function testNoHost()
    {
        $this->assertSame(Dsn::create('test')->scheme, 'test');
        $this->assertSame(Dsn::create('test')->hostname, null);
    }

    /**
     * @expectedException Exception
     */
    public function testException()
    {
        new Dsn('12344://?ee');
    }


    public function testToString()
    {
        $dsn = 'http://user:password@host:34/path1/path2/?param1=23&param2=34';
        $this->assertSame($dsn, (string)Dsn::create($dsn));

        $dsn = 'http://user@host:34/?param1=23&param2=34';
        $this->assertSame($dsn, (string)Dsn::create($dsn));

    }

    public function testShortScheme()
    {
        $dsn = new Dsn('test://');
        $this->assertSame('test', $dsn->scheme);
        $this->assertSame(null, $dsn->hostname);
    }

    public function testNull()
    {
        $this->assertSame(true, Dsn::create() instanceof Dsn);
    }
}