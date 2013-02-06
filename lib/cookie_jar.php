<?php
namespace Symphony\Core;

class CookieJar implements \ArrayAccess
{
    protected $cookies = array();
    protected $cookie_obj;

    public function __construct(Cookie $cookie_obj = null)
    {
        if (!is_null($cookie_obj)) {
            $this->cookie_obj = $cookie_obj;
        }
        else {
            $this->cookie_obj = new Cookie('foo');
        }
    }

    public function find($name)
    {
        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }
        else {
            return null;
        }
    }

    public function add(Cookie $cookie)
    {
        $this->cookies[$cookie->name] = $cookie;
    }

    public function getCount()
    {
        return count($this->cookies);
    }

    public function create($name, $value = null)
    {
        $obj = clone $this->cookie_obj;
        $obj->name = $name;
        $obj->value = $value;
        return $obj;
    }

    /**
     *  ArrayAccess Methods.
     *  These have the sole purpose of making accessing cookies as simple as usual.
     **/

    public function offsetExists($name) {
        return isset($this->cookies[$name]);
    }

    public function offsetGet($name) {
        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }
        $return_obj = clone $this;
        foreach ($this->cookies as $cookie) {
            if (preg_match(sprintf('/^%s\[(.*)\]$/', $name), $cookie->name, $name) === 1) {
                $return_cookie = clone $cookie;
                $return_cookie->name = $name[1];
                $return_obj->add($return_cookie);
            }
        }
        
        if ($return_obj->getCount() > 0) {
            return $return_obj;
        }
        
        return null;
    }

    public function offsetSet($name, $value) {
        throw new \Exception('The Cookie array is read only. To create new cookies use the CookieJar object');
    }

    public function offsetUnset($name) {
        throw new \Exception('The Cookie array is read only. To delete/unset cookies use the CookieJar object');
    }
}