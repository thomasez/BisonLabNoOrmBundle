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
     */

    private $_schema = array();
    private $id;
    private $id_key;

    public function __construct($data = array(), $schema = array())
    {
        if (empty($schema)) {
            if (empty(static::$model_setup)) {
                throw new \Exception("This object type, BaseModelSchema requires a defined schema to be able to handle itself.");
                return null;
            } else {
                // Maybe some convert instead? foreach with addToSchema?
                $this->_schema = static::$model_setup;
            }
        }

        $this->id_key = static::$id_key;

        foreach ($this->_schema as $key) {
            if (isset($data[$key])) {
                $this->$key = $data[$key];
            } else {
                $this->$key = null;
            }
        }
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
        $this[$this->id_key] = $identifier_value;
    }

    /**
     * Returns the unique identifier value for this object, usually the value
     * of an $id property, $<objecttype>Id or similar
     * 
     * @return mixed
     */
    public function getDataArrayIdentifierValue()
    {
        return $this[$this->id_key];
    }
    
    public function hasDataArrayIdentifierValue()
    {
        return isset($this->$this->id_key) ? true : null;
    }
    
    /*
     * Functions implementing ArrayAccess
     */

    public function toDataArray()
    {
        $simple_array = array();

        foreach ($this as $key => $value) {
            if ($key == $this->id_key) {
                continue;
            }
            $simple_array[$key] = $value;
        }
        return $simple_array;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_schema);
    }

    public function offsetGet($offset)
    {
        if ($offset != 'id' && !array_key_exists($offset, $this->_schema)) {
            throw new \Exception("The property {$offset} doesn't exist");
        } elseif (!isset($this->$offset)) {
            return null;
        }

        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {

        if ($offset != 'id' && !array_key_exists($offset, $this->_schema)) {
            throw new \Exception("The property {$offset} doesn't exist");
        }

        $this->_schema[$offset] = true;
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {

        if ($offset != 'id' && !array_key_exists($offset, $this->_schema)) {
            throw new \Exception("The property {$offset} doesn't exist");
        }

        // Should I really do this? unset is unset so I guess so, for now.
        unset($this->_schema[$key]);

        unset($this->$offsetSet);
        
        // $this->$offsetSet($offset, null);
    }

}

