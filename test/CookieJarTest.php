<?php

use \Symphony\Core\CookieJar as CookieJar;
use \Symphony\Core\Cookie as Cookie;

require_once('lib/cookie_jar.php');
require_once('lib/cookie.php');

class CookieJarTest extends PHPUnit_Framework_TestCase
{

    public function testInitiallyEmpty()
    {
        $cookie = new Cookie('foo');
        $jar = new CookieJar($cookie);
        $this->assertEquals(0, $jar->getCount());
        return $jar;
    }

    public function testCreateWithoutTemplate()
    {
        $jar = new CookieJar();
        $this->assertEquals(0, $jar->getCount());
    }

    /**
     * @depends testInitiallyEmpty
     */
    public function testAddCookies(CookieJar $jar)
    {
        $cookie = new Cookie('foo');
        $jar->add($cookie);
        $this->assertEquals(1, $jar->getCount());

        $cookie = new Cookie('bar');
        $jar->add($cookie);
        $this->assertEquals(2, $jar->getCount());

        return $jar;
    }

    /**
     * @depends testAddCookies
     */
    public function testFindCookie(CookieJar $jar)
    {
        $cookie = $jar->find('foo');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('foo', $cookie->name);
        $this->assertEquals(null, $cookie->value);

        $cookie = $jar->find('bar');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('bar', $cookie->name);
        $this->assertEquals(null, $cookie->value);

        return $jar;
    }

    /**
     * @depends testInitiallyEmpty
     */
    public function testNewCookieOnlyName(CookieJar $jar)
    {
        $cookie = $jar->create('bar');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('bar', $cookie->name);
        $this->assertEquals(null, $cookie->value);
        return $jar;
    }

    /**
     * @depends testInitiallyEmpty
     */
    public function testNewCookieNameAndValue(CookieJar $jar)
    {
        $cookie = $jar->create('foo', 'bar');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('foo', $cookie->name);
        $this->assertEquals('bar', $cookie->value);
        return $jar;
    }

    public function testJarAsSimpleCookieArray()
    {
        $jar = new CookieJar();
        $cookie = new Cookie('symphony', 'rocks');
        $jar->add($cookie);
        $_COOKIE = $jar;
        $this->assertEquals($cookie, $_COOKIE['symphony']);
    }

    public function testJarAsMultiDimensionalCookieArray()
    {
        $jar = new CookieJar();
        $set_cookie = new Cookie('symphony[websites]', 'rock');
        $jar->add($set_cookie);

        $_COOKIE = $jar;
        $get_cookie = $_COOKIE['symphony']['websites'];

        $this->assertInstanceOf('\Symphony\Core\Cookie', $get_cookie);
        $this->assertEquals('websites', $get_cookie->name);
    }

    public function testSessionArrayWriteThrowsException()
    {
        $jar = new CookieJar();
        $set_cookie = new Cookie('symphony', 'rocks');
        $jar->add($set_cookie);

        $_COOKIE = $jar;

        $this->setExpectedException('Exception');
        $_COOKIE['symphony'] = 'stupid';
    }

    public function testSessionArrayDeleteThrowsException()
    {
        $jar = new CookieJar();
        $set_cookie = new Cookie('symphony', 'rocks');
        $jar->add($set_cookie);

        $_COOKIE = $jar;

        $this->setExpectedException('Exception');
        unset($_COOKIE['symphony']);
    }
}