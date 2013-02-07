<?php

use \Symphony\Core\Cookie;

require_once('lib/cookie.php');

class CookieTest extends PHPUnit_Framework_TestCase
{
    public function testSettingIndexAndNameAndValue()
    {
        $cookie = new Cookie('index', 'foo', 'bar');
        $this->assertEquals('index', $cookie->get('index'));
        $this->assertEquals('foo', $cookie->get('name'));
        $this->assertEquals('bar', $cookie->get('value'));
    }

    public function testSettingParameters(){
        $properties = array(
            'max_age'   => 100,
            'domain'    => 'example.com',
            'path'      => '/symphony',
            'secure'    => true,
            'http_only' => true
        );
        $cookie = new Cookie('index', 'name', 'value', $properties);
        $this->assertEquals('index', $cookie->get('index'));
        $this->assertEquals('name', $cookie->get('name'));
        $this->assertEquals('value', $cookie->get('value'));
        $this->assertEquals(100, $cookie->get('max_age'));
        $this->assertEquals('example.com', $cookie->get('domain'));
        $this->assertEquals('/symphony', $cookie->get('path'));
        $this->assertEquals(true, $cookie->get('secure'));
        $this->assertEquals(true, $cookie->get('http_only'));
    }

    /**
     * @dataProvider invalidForEverything
     * @expectedException Exception
     */
    public function testSettingInvalidIndexThrowsException($invalid_index)
    {
        $cookie = new Cookie($invalid_index, 'good_value');
    }

    /**
     * @dataProvider invalidForEverything
     * @expectedException Exception
     */
    public function testSettingInvalidNameThrowsException($invalid_name)
    {
        $cookie = new Cookie('valid_index', $invalid_name);
    }

    /**
     * @dataProvider invalidForEverything
     * @expectedException Exception
     */
    public function testSettingInvalidValueThrowsException($invalid_value)
    {
        $cookie = new Cookie('valid_index', 'valid_name', $invalid_value);
    }

    /**
     * @expectedException Exception
     */
    public function testSettingUndefinedPropertyThrowsException()
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->set('does_not', 'exist');
    }

    public function testGettingUndefinedPropertyReturnsNull()
    {
        $cookie = new Cookie('foo', 'bar');
        $this->assertNull($cookie->get('does_not_exist'));
    }
 
    /**
     * @expectedException Exception
     */
    public function testSettingInvalidMaxAgeThrowsException()
    {
        $cookie = new Cookie('foo', 'bar');
        $cookie->set('max_age', 'invalid');
    }
 
    public function testAttributeExistsInHeaderStringWhenSet()
    {
        $properties = array(
            'max_age'   => 13023,
            'domain'    => 'notarealdomain.com',
            'path'      => '/awesome',
            'secure'    => true,
            'http_only' => true
        );
        $cookie = new Cookie('foo', 'bar', 'value', $properties);
        $header_string = $cookie->getHeaderString();
        $this->assertContains(sprintf('%s[%s]=%s', 'foo', 'bar', 'value'), $header_string);
        $this->assertContains(sprintf('Domain=%s', $properties['domain']), $header_string);
        $this->assertContains(sprintf('Path=%s', $properties['path']), $header_string);
        $this->assertContains('Secure', $header_string);
        $this->assertContains('HttpOnly', $header_string);
    }

    public function testSecureAndHttpOnlyOnlySetWhenTrue()
    {
        $cookie = new Cookie('index', 'name', 'value');
        $cookie->set('http_only', false);
        $cookie->set('secure', false);
        $header_string = $cookie->getHeaderString();
        $this->assertNotContains('Secure', $header_string);
        $this->assertNotContains('HttpOnly', $header_string);
    }
 
    public function testCastingToStringYieldsValue()
    {
        $cookie = new Cookie('my_index', 'my_name', 'my_value');
        $this->assertEquals('my_value', (string)$cookie);
    }
 
    public function invalidForEverything()
    {
        return array(
            array('space '),
            array('colon;'),
            array("tab\t"),
            array("newline\n"),
            array("newline\r"),
            array('equals=')
        );
    }
}