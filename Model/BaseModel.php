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

    /*
     * Functions implementing ArrayAccess
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
