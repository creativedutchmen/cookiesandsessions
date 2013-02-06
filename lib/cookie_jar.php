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

    public function find($index, $name)
    {
        if (isset($this->cookies[$index][$name])) {
            return $this->cookies[$index][$name];
        }
        else {
            return null;
        }
    }

    public function add(Cookie $cookie)
    {
        $this->cookies[$cookie->index][$cookie->name] = $cookie;
    }

    public function getCount()
    {
        return count($this->cookies, 1) - count($this->cookies, 0);
    }

    public function create($name, $value = null, array $options = array())
    {
        $obj = clone $this->cookie_obj;
        $obj->name = $name;
        $obj->value = $value;
        foreach ($options as $option => $value) {
            $obj->$option = $value;
        }
        return $obj;
    }

    /**
     *  ArrayAccess Methods.
     *  These have the sole purpose of making accessing cookies as simple as usual.
     **/

    public function offsetExists($offset) {
        return isset($this->cookies[$offset]);
    }

    public function offsetGet($offset) {
        if (isset($this->cookies[$offset])) {
            return $this->cookies[$offset];
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