<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Model;

abstract class BaseModelSchema implements StorableObjectInterface, \ArrayAccess
{
    /*
     * This is for the "Dynamic Schema" stuff. Insert a schema when you 
     * construct and it will be used as the object properties, forms, validation
     * and so on.
     *
     * I'll keep the option to have a schema as a static in the inheriting model.
     *
     * The schema will continue to be called model_setup since well, that's
     * what it is.
     */

    private $_metadata = array();
    private $id;
    private $_id_key;

    public function __construct($data = array(), $metadata = array())
    {

        $this->_metadata = $metadata;

        if (empty($metadata['schema'])) {
            if (empty(static::$model_setup)) {
                throw new \Exception("This object type, BaseModelSchema requires a defined schema to be able to handle itself.");
                return null;
            } else {
                // Maybe some convert instead? foreach with addToSchema?
                $this->_metadata['schema'] = static::$model_setup;
            }
        } 

        // Gotta check out what I meant with this one..
        $this->_id_key = static::$id_key;
        $this->id = $data[static::$id_key];

        foreach ($this->_metadata['schema'] as $key => $definition) {
            if (isset($data[$key])) {
                $this->$key = $data[$key];
            } else {
                $this->$key = null;
            }
        }

    }

    public function getSchema() {
        return $this->_metadata['schema'];
    }

    public function fromDataArray($data, \RedpillLinpro\NosqlBundle\Manager\BaseManager $manager)
    {
        foreach ($data as $key => $val)
        {
            $this->$key = $val;
        }
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /*
     * Statics.
     */

    static function getFormSetup()
    {
        return static::$model_setup;
    }

    static function getClassName()
    {
        return static::$classname;
    }

    /**
     * Set the unique identifier value for this object
     * 
     * This method is used by the manager to set the identifier value to the
     * value retrieved from the remote call after storing this object
     * 
     * @param mixed $identifier_value 
     */
    public function setDataArrayIdentifierValue($identifier_value)
    {
        $this[$this->_id_key] = $identifier_value;
    }

    /**
     * Returns the unique identifier value for this object, usually the value
     * of an $id property, $<objecttype>Id or similar
     * 
     * @return mixed
     */
    public function getDataArrayIdentifierValue()
    {
        return $this[$this->_id_key];
    }
    
    public function hasDataArrayIdentifierValue()
    {
        return isset($this->$this->_id_key) ? true : null;
    }
    
    /*
     * Functions implementing ArrayAccess
     */

    public function toDataArray()
    {
        $simple_array = array();

        foreach ($this as $key => $value) {
            // Why this? Not even I remember.. 
            // if ($key == $this->_id_key) {
            // But the id_key itself is not needed.
            if ($key == "_id_key") {
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

    public function offsetGet($offset)
    {
        if ($offset != 'id' && !array_key_exists($offset, $this->_metadata['schema'])) {
            throw new \Exception("The property {$offset} doesn't exist");
        } elseif (!isset($this->$offset)) {
            return null;
        }

        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {

        if ($offset != 'id' && !array_key_exists($offset, $this->_metadata['schema'])) {
            throw new \Exception("The property {$offset} doesn't exist");
        }

        $this->_metadata['schema'][$offset] = true;
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {

        if ($offset != 'id' && !array_key_exists($offset, $this->_metadata['schema'])) {
            throw new \Exception("The property {$offset} doesn't exist");
        }

        // Should I really do this? unset is unset so I guess so, for now.
        unset($this->_metadata['schema'][$key]);

        unset($this->$offsetSet);
        
        // $this->$offsetSet($offset, null);
    }

}

