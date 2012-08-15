<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Model;

abstract class BaseModelDynamic implements StorableObjectInterface, \ArrayAccess
{
    /*
     * This is the model version for objects / data that has no defined schema.
     * Basically, it just eat what it gets.
     */

    /* We do have a schema but it's basically a key store. We do need
     * the keys and also have a function for returning a schema so we can
     * show the data and also simple forms.
     */
    private $_schema = array();
    private $id;
    private $id_key;

    public function __construct($data = array())
    {
        $this->id_key = static::$id_key;
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

        $this->_schema[$key] = array('FormType' => 'text');
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

    function getFormSetup()
    {
        return static::$model_setup;
    }

    /*
     * Statics.
     */

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
        return isset($this->$offset) ? $this->$offset : null;
    }

    /* We'll swallow everything in this setup. */
    public function offsetSet($offset, $value)
    {
        $this->addToSchema($offset);
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        // Should I really do this? unset is unset so I guess so, for now.
        unset($this->_schema[$key]);

        // Will this work or do we end up with a recurseloop?
        unset($this->$offset);
        // ALternative: $this->$offset = null;
    }

}

