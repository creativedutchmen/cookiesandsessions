<?php

use \Symphony\Core\Cookie;

require_once('lib/cookie.php');

class CookieTest extends PHPUnit_Framework_TestCase
{
    var $defaults = array(
		'max_age'       => 1209600,
		'domain'        => HTTP_HOST,
		'path'          => '/',
		'secure'        => false,
		'http_only'     => __SECURE__,
		'index'         => 'Symphony'
	);
    /**
     * @dataProvider validNamesAndValues
     */
    public function testSettingNameAndValue($name, $value)
    {
        $cookie = new Cookie($name, $value);
        $this->assertEquals($cookie->name, $name);
        $this->assertEquals($cookie->value, $value);
    }

	public function testInitializeDefaults()
	{
        $cookie = new Cookie('name', 'value');
        $this->checkCookieValues($cookie, $this->defaults);
	}

	public	function testCreateWithParameters()
	{
		$values = array(
			'max_age'       => 60,
			'domain'        => 'symphony-cms.com',
			'path'          => '/',
			'secure'        => true,
			'http_only'     => true,
			'index'         => 'Cookie'
		);
		$cookie = new Cookie('name', 'value', $values);
		$this->checkCookieValues($cookie, $values);
	}

	public function testIncludesDefaultsInHeaderString()
	{
	    $name = 'empty';
	    $value = 'value';
		$cookie = new Cookie($name, $value);
		$header_string = $cookie->getHeaderString();
        $this->assertContains('Set-Cookie', $header_string);
        $this->assertContains(sprintf('%s[%s]=%s', $this->defaults['index'], $name, $value), $header_string);
        $this->assertContains(sprintf('Domain=%s', $this->defaults['domain']), $header_string);
        $this->assertContains(sprintf('Path=%s', $this->defaults['path']), $header_string);
        $this->assertContains(sprintf('Max-Age=%s', $this->defaults['max_age']), $header_string);
        $this->assertNotContains('HttpOnly', $header_string);
        $this->assertNotContains('Secure', $header_string);
	}

	public function testIncludesParametersInHeaderString()
	{
	    $name = 'empty';
	    $value = 'value';
	    $values = array(
			'max_age'       => 60,
			'domain'        => 'symphony-cms.com',
			'path'          => '/',
			'secure'        => true,
			'http_only'     => true,
			'index'         => 'Cookie'
		);
		$cookie = new Cookie($name, $value, $values);
		$header_string = $cookie->getHeaderString();
        $this->assertContains('Set-Cookie', $header_string);
        $this->assertContains(sprintf('%s[%s]=%s', $values['index'], $name, $value), $header_string);
        $this->assertContains(sprintf('Domain=%s', $values['domain']), $header_string);
        $this->assertContains(sprintf('Path=%s', $values['path']), $header_string);
        $this->assertContains(sprintf('Max-Age=%s', $values['max_age']), $header_string);
        $this->assertContains('HttpOnly', $header_string);
        $this->assertContains('Secure', $header_string);
	}

    /**
     * @dataProvider invalidCharacters
     */
    public function testCreatingCookieWithInvalidCharactersThrowsException($invalid_name)
    {
        $this->setExpectedException('Exception');
        $cookie = new Cookie($invalid_name, 'good_value');
        $this->setExpectedException('Exception');
        $cookie = new Cookie('good_name', $invalid_characters);
        
        $cookie = new Cookie('good_name', 'good_value');
        $this->setExpectedException('Exception');
        $cookie->name = $invalid_characters;
    }

    public function testSettingUndefinedParametersThrowsException()
    {
        $cookie = new Cookie('good','value');
        $this->setExpectedException('Exception');
        $cookie->invalidProperty = 'test';
        
        $this->setExpectedException('Exception');
        $cookie = new Cookie('good','value',array('bogus'=>'value'));
    }

    public function testGettingUndefinedParametersThrowsException()
    {
        $cookie = new Cookie('good','value');
        $this->setExpectedException('Exception');
        $error = $cookie->invalidProperty;
    }

    public function testCastingCookieToStringReturnsValue()
    {
        $value = 'Symphony_Cookie';
        $cookie = new Cookie('name', $value);
        $this->assertEquals($value, (string)$cookie);
    }

    public static function invalidCharacters()
    {
        return array(
            array('space '),
            array('comma,'),
            array('equals='),
            array('semicolon;'),
            array("newline\n"),
            array("tab\t"),
            array("newline\r")
        );
    }

    public static function validNamesAndValues()
    {
        return array(
            array('name', 'value'),
            array('anothername', 'anothervalue'),
            array('sensible_name', 'sensible_value'),
            array('00numericname', '09numericvalue'),
            array('CapitalName', 'CapitalValue'),
            array('weird_characters-\'\")*%[]', 'weird_characters-\'\")*%[]')
        );
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