<?php
namespace Symphony\Core;
/**
 * Cookie class. Provides a read/write interface for cookies.
 *
 * @package Symphony\Core
 * @author Huib Keemink
 **/
class Cookie
{
    /**
     * Expiration time, in seconds.
     * 
     * Note that in the __toString method we include both Max-Age and Expires, but Expires has been deprecated in HTML1.1
     * The only reason for still including Expires is that IE[1-7-8] do not support the Expires flag.
     * For now, Max-Age is the preferred way of setting the age. When IE chooses to support it, we can remove the Expires flag.
     *
     * @var int
     **/
    protected $max_age = 1209600;

    /**
     * Domain, defaults to the Symphony host
     * @var string
     **/
    protected $domain = HTTP_HOST;

    /**
     * Path, defaults to the Symphony path.
     * @var string
     **/
    protected $path = '/';

    /**
     * A secure cookie has the secure attribute enabled and is only used via HTTPS, ensuring that the cookie is always encrypted when transmitting from client to server. This makes the cookie less likely to be exposed to cookie theft via eavesdropping.
     * @var boolean
     **/
    protected $secure = __SECURE__;

    /**
     * On a supported browser, an HttpOnly session cookie will be used only when transmitting HTTP (or HTTPS) requests, thus restricting access from other, non-HTTP APIs (such as JavaScript). This restriction mitigates but does not eliminate the threat of session cookie theft via cross-site scripting (XSS). This feature applies only to session-management cookies, and not other browser cookies.
     * @var boolean
     **/
    protected $http_only = false;

    /**
     * The Cookie Index. This is used to differentiate extensions from the core, and extensions from each other.
     * It allows developers to use "normal names" in their extension, as long as they pick a unique index.
     * Much like namespaces, if you will.
     *
     * @var string
     **/
    protected $index = 'Symphony';

    /**
     * The cookie key. Duplicate keys will be overwritten.
     *
     * @var string
     **/
    protected $name;

    /**
     * undocumented class variable
     *
     * @var string
     **/
    protected $value;

    /**
     * Constructor.
     * 
     * @param  array
     * @return void
     **/
    public function __construct($index, $name, $value = null, $properties = array())
    {
        $this->set('index', $index);
    	$this->set('name', $name);
    	$this->set('value', $value);
    	foreach ($properties as $property => $value) {
    	    $this->set($property, $value);
    	}
    }

    /**
     * Get method for class attributes. 
     *
     * @return mixed
     **/
    public function get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }

    /**
     * undocumented function
     *
     * @return void
     * @author Huib Keemink
     **/
    protected function setMaxAge($max_age)
    {
        if (!is_int($max_age)) {
            throw new \Exception(sprintf('Max Age must be a number, given %s', gettype($max_age)));
        }
        $this->max_age = $max_age;
    }
    
    /**
     * Get method for class attributes. 
     *
     * @return mixed
     **/
    public function set($name, $value)
    {
        $this->validateString($value);
        $set_method = sprintf('set%s', str_replace(' ','', ucwords(str_replace('_', ' ', $name))));
        if (method_exists($this, $set_method)) {
            call_user_func_array(array($this, $set_method), array($value));
            return;
        }
        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        }
        throw new \Exception(sprintf('Attribute %s can not be found', $name));
    }

    protected function validateString($string)
    {
        if (preg_match('/\s/', $string) === 1) {
            throw new \Exception('Whitespaces are not allowed in a cookie');
        }
        
        $invalid_characters = array(
            '=',
            ';'
        );
        foreach ($invalid_characters as $character) {
            if (strpos($string, $character) !== FALSE) {
                throw new \Exception(sprintf('The character %s is not allowed in a cookie', $character));
            }
        }
    }

    /**
     * Converts the Cookie object to a header string.
     *
     * @return string
     **/
    public function getHeaderString()
    {
    	return sprintf(
    		'Set-Cookie: %s[%s]=%s; Expires=%s; Max-Age=%d; Domain=%s; Path=%s%s%s',
    		$this->index,
    		$this->name,
    		$this->value,
    		gmdate('D, d M Y H:i:s T', time() + $this->max_age),
    		$this->max_age,
    		$this->domain,
    		$this->path,
    		($this->secure)?'; Secure':'',
    		($this->http_only)?'; HttpOnly':''
    	);
    }

    /**
     * To String. Returns the value as if the cookie is an entry in the $_COOKIE array.
     *
     * @return string
     **/
    public function __toString()
    {
        return $this->value;
    }
}