<?php

use \Symphony\Core\CookieJar as CookieJar;
use \Symphony\Core\Cookie as Cookie;

require_once('lib/cookie_jar.php');
require_once('lib/cookie.php');

class CookieJarTest extends PHPUnit_Framework_TestCase
{

    public function testInitiallyEmpty()
    {
        $cookie = new Cookie('foo', 'bar');
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
        $cookie = new Cookie('Symphony','bar');
        $jar->add($cookie);
        $this->assertEquals(1, $jar->getCount());

        $cookie = new Cookie('Symphony', 'foo');
        $jar->add($cookie);
        $this->assertEquals(2, $jar->getCount());

        $cookie = new Cookie('pets', 'dog');
        $jar->add($cookie);
        $this->assertEquals(3, $jar->getCount());

        $cookie = new Cookie('pets', 'cat');
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
        $this->assertEquals('foo', $cookie->get('name'));
        $this->assertEquals(null, $cookie->get('value'));

        $cookie = $jar->find('Symphony', 'bar');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('bar', $cookie->get('name'));
        $this->assertEquals(null, $cookie->get('value'));

        $cookie = $jar->find('DoesNot', 'Exist');
        $this->assertEmpty($cookie);

        return $jar;
    }

    /**
     * @depends testInitiallyEmpty
     */
    public function testNewCookieOnlyIndexAndName(CookieJar $jar)
    {
        $cookie = $jar->create('foo', 'bar');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('bar', $cookie->get('name'));
        $this->assertEquals(null, $cookie->get('value'));
        return $jar;
    }

    /**
     * @depends testInitiallyEmpty
     */
    public function testNewCookieIndexNameAndValue(CookieJar $jar)
    {
        $cookie = $jar->create('index', 'foo', 'bar');
        $this->assertInstanceOf('\Symphony\Core\Cookie', $cookie);
        $this->assertEquals('index', $cookie->get('index'));
        $this->assertEquals('foo', $cookie->get('name'));
        $this->assertEquals('bar', $cookie->get('value'));
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
        $baked = $jar->create('index', 'foo', 'bar', $options);
        $cookie = new Cookie('index', 'foo', 'bar', $options);
        $this->assertEquals($cookie, $baked);
    }

    public function testJarAsSimpleCookieArray()
    {
        $jar = new CookieJar();
        $cookie = new Cookie('Symphony', 'websites', 'rock');
        $jar->add($cookie);
        $_COOKIE = $jar;
        $this->assertEquals($cookie, $_COOKIE['Symphony']['websites']);
        $this->assertEmpty($_COOKIE['DoesNot']['Exist']);
        $this->assertTrue(isset($_COOKIE['Symphony']));
        $this->assertFalse(isset($_COOKIE['DoesNot']));
    }

    public function testJarAsMultiDimensionalCookieArray()
    {
        $jar = new CookieJar();
        $set_cookie = new Cookie('Other', 'websites', 'rock_less');
        $jar->add($set_cookie);

        $_COOKIE = $jar;
        $get_cookie = $_COOKIE['Other']['websites'];

        $this->assertInstanceOf('\Symphony\Core\Cookie', $get_cookie);
        $this->assertEquals('websites', $get_cookie->get('name'));
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