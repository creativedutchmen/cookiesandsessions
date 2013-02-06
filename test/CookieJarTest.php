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

        $cookie = new Cookie('dog');
        $cookie->index = 'pets';
        $jar->add($cookie);
        $this->assertEquals(3, $jar->getCount());

        $cookie = new Cookie('cat');
        $cookie->index = 'pets';
        $jar->add($cookie);
        $this->assertEquals(4, $jar->getCount());

        return $jar;
    }

    /**
     * @depends testAddCookies
     */
    public function testFindCookie(CookieJar $jar)
    {
        $cookie = $jar->find('Symphony', 'foo');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('foo', $cookie->name);
        $this->assertEquals(null, $cookie->value);

        $cookie = $jar->find('Symphony', 'bar');
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

    /**
     * @depends testInitiallyEmpty
     */
    public function testNewCookieNameValueAndOptions(CookieJar $jar)
    {
        $options = array(
			'max_age'       => 60,
			'domain'        => 'symphony-cms.com',
			'path'          => '/',
			'secure'        => true,
			'http_only'     => true,
			'index'         => 'Cookie'
		);
        $cookie = $jar->create('foo', 'bar', $options);
        $this->checkCookieValues($cookie, $options);
    }

    public function testJarAsSimpleCookieArray()
    {
        $jar = new CookieJar();
        $cookie = new Cookie('websites', 'rock');
        $jar->add($cookie);
        $_COOKIE = $jar;
        $this->assertEquals($cookie, $_COOKIE['Symphony']['websites']);
    }

    public function testJarAsMultiDimensionalCookieArray()
    {
        $jar = new CookieJar();
        $set_cookie = new Cookie('websites', 'rock_less');
        $set_cookie->index = 'Other';
        $jar->add($set_cookie);

        $_COOKIE = $jar;
        $get_cookie = $_COOKIE['Other']['websites'];

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

    public function checkCookieValues($cookie, $values)
    {
        $this->assertEquals($values['max_age'], $cookie->max_age);
		$this->assertEquals($values['domain'], $cookie->domain);
		$this->assertEquals($values['path'], $cookie->path);
		$this->assertEquals($values['secure'], $cookie->secure);
		$this->assertEquals($values['http_only'], $cookie->http_only);
		$this->assertEquals($values['index'], $cookie->index);
    }
}