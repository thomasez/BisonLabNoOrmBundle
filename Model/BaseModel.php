<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Model;

abstract class BaseModel
{

    protected $_metadata = array();
    protected $id;
    protected $_id_key;

    /*
     * Functions implementing ArrayAccess
     */

    /*
     * This one returns only the data part of the object. I'm not sure it's the
     * right thing to do and that I'd rather have "completeArray" and a
     * stripped "toArray" instead
     */
    public function toDataArray()
    {
        $simple_array = array();

        foreach ($this as $key => $value) {
            if ($key == $this->_id_key) {
                continue;
            }
            // Do I want this one? I think so. The metadata isn't a part of the
            // data is it?
            if ($key == "_metadata") {
                continue;
            }
            $simple_array[$key] = $value;
        }
        return $simple_array;
    }

    public function toCompleteArray()
    {
        $simple_array = array();

        foreach ($this as $key => $value) {
            $simple_array[$key] = $value;
        }
        return $simple_array;
    }

    public function __call($name, $args = null)
    {
        if (preg_match("/^get(\w+)/i", $name, $matches)) {
            return $this->offsetGet($matches[1]);
        } elseif (preg_match("/^set(\w+)/i", $name, $matches)) {
            return $this->offsetSet($matches[1], $args[0]);
        }
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_metadata['schema']);
    }

}
