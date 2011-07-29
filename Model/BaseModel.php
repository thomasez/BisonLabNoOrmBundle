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

    public function __construct($data = array())
    {
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
      if (array_key_exists($offset, static::$model_setup))
      {
        return $this->$offset;
      }
      throw new \Exception("The property {$offset} doesn't exist");
    }

    public function offsetSet($offset, $value)
    {
      if (array_key_exists($offset, static::$model_setup))
      {
        $this->$offset = $value;;
      }
      else
      {
        throw new \Exception("The property {$offset} doesn't exist");
      }
    }

    public function offsetUnset($offset)
    {
      $this->$offsetSet($offset, null);
    }

}

