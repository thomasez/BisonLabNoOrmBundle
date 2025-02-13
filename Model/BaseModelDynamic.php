<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Model;

#[\AllowDynamicProperties]
abstract class BaseModelDynamic extends BaseModel implements StorableObjectInterface, \ArrayAccess
{
    /*
     * This is the model version for objects / data that has no defined schema.
     * Basically, it just eat what it gets.
     */

    /* We do have a schema but it's basically a key store. We do need
     * the keys and also have a function for returning a schema so we can
     * show the data and also simple forms.
     */
    public function __construct($data = array(), $metadata = array())
    {
        $this->_metadata = $metadata;
        $this->_id_key = static::$id_key;
        $this->id = null;
        foreach ($data as $key => $val) {
            $this->$key = $val;
            $this->addToSchema($key);
        }
    }

    public function addToSchema($key)
    {
        // We'll define _variables as private and not for our schema.
        if (preg_match("/^_/", $key)) { return; }

        $this->_metadata['schema'][$key] = array('FormType' => 'text', 'Validator' => array());
    }

    public function fromDataArray($data, \BisonLab\NoOrmBundle\Manager\BaseManager $manager)
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

    function getFormSetup()
    {
        return $this->_metadata['schema'];
    }

    /*
     * Statics.
     */

    /**
     *
     * Need to get the ID key for the adapter/service to grab the 
     * correct stuff. 
     * 
     * @return string?
     */
    static function getIdKey()
    {
        return static::$id_key;
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
     * __call, toDataArray and offsetExists is in BaseModel.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->$offset) ? $this->$offset : null;
    }

    /* We'll swallow everything in this setup. */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->addToSchema($offset);
        // I cannot add "integer" to the schema since it may not be meant as
        // being an integer all the time.
        // Reason for doing this is that if it's stored as a string in Mongo, it
        // won't be found when you search with an int. 
        if (is_numeric($value) ) {
            $this->$offset = (int)$value;
        } else {
            $this->$offset = $value;
        }
        $this->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        // Should I really do this? unset is unset so I guess so, for now.
        unset($this->_metadata['schema'][$key]);

        // Will this work or do we end up with a recurseloop?
        unset($this->$offset);
    }
}
