<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace RedpillLinpro\NosqlBundle\Model;

abstract class BaseModel implements \ArrayAccess
{

    public $id;

    /*
     * This one might be named wrongly so I'll try to describe what
     * it does.
     * If there are no model_setup we should presume the user of this Bundle
     * wants it all, which means we should drop the key-check 
     * and swallow everything instead.
     */
    private $_strict_model = false;

    public function __construct($data = array())
    {
      if (empty(static::$model_setup)) 
      {
        $this->_strict_model = false;
        foreach ($data as $key => $val)
        {
          $this->$key = $val;
        } 
      }
      else
      {
        $this->_strict_model = true;
        foreach (static::$model_setup as $key => $val)
        {
          if (isset($data[$key]))
          {
            $this->$key = $data[$key];
          }
          else
          {
          $this->$key = null;
          }
        }
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

    /*
     * Functions implementing ArrayAccess
     */

    public function toSimpleArray()
    {
      $simple_array = array();
      foreach (array_keys(static::$model_setup) as $key)
      {
        $simple_array[$key] = $this->$key;
      }

      return $simple_array;
    }

    public function offsetExists($offset)
    {
      return array_key_exists($offset, static::$model_setup);
    }

    public function offsetGet($offset)
    {
      if ( $this->_strict_model 
            && !array_key_exists($offset, static::$model_setup))
      {
        throw new \Exception("The property {$offset} doesn't exist");
      }

      return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
      if ( $this->_strict_model 
            && !array_key_exists($offset, static::$model_setup))
      {
        throw new \Exception("The property {$offset} doesn't exist");
      }

      $this->$offset = $value;;

    }

    public function offsetUnset($offset)
    {

      if ( $this->_strict_model 
            && !array_key_exists($offset, static::$model_setup))
      {
        throw new \Exception("The property {$offset} doesn't exist");
      }

      $this->$offsetSet($offset, null);

    }

}

