<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace RedpillLinpro\NosqlBundle\Manager;

abstract class BaseManagerMongo
{
  /* 
   * Remember these.
   * We might even do this simple and just use one name, for the Manager, Model
   * and MongoDB collection.
   * Right now they are all the same but I define different names here.
   * Or rather, they have to be defined in the object extending this one.
   */
  // protected static $collection  = 'Base';
  // protected static $model       = 'Model\Base';

  protected $simple_mongo;

  public function __construct($simple_mongo)
  {
    $this->simple_mongo = $simple_mongo;
  }

  /*
   * Finders
   */
  public function findAll()
  {

    $objects = array();
    foreach ($this->simple_mongo->findAll(static::$collection) as $o)
    {
      $object = new static::$model($o);
      $object->setId($o['_id']);
      $objects[] = $object;
    }

    return $objects;

  }

  public function findOneById($id)
  {
    $data = $this->simple_mongo->findOneById(
        static::$collection, $id);

    if (!$data)
    {
      return null;
    }

    $object = new static::$model($data);
    // $object = new Model\Contract($data);
    $object->setId($data['_id']);

    return $object;
  }

  public function findByKeyVal($key, $val)
  {
    $objects = array();

    foreach ($this->simple_mongo->findByKeyVal(
        static::$collection, $key, $val) as $o)
    {
      $object = new static::$model($data);
      $object->setId($o['_id']);
      $objects[] = $object;
    }

    return $objects;
  }

  public function save($object)
  {
    if ($object->getClassName() != static::$collection)
    {
      throw new \InvalidArgumentExceptio('This is not an object I can save');
    }

    $object_data = $object->toSimpleArray();

    if ($object->getId())
    {
      $object_data['_id'] = $object->getId();
    }

    // Save can do both insert and update with MongoDB.
    $new_data = $this->simple_mongo->save($object_data, static::$collection);
    if (isset($new_data['_id']))
    {
      $object->setId($new_data['_id']);
    }

    return $object;

  }

  public function delete($object)
  {

    if (is_object($object))
    {
      if ($object->getClassName() != static::$collection)
      {
        throw new \InvalidArgumentExceptio('This is not an object I can save');
      }

      if ($object->getId())
      {
        $id = $object->getId();
      }
    }
    else
    {
       $id = $object; 
    }

    // Save can do both insert and update with MongoDB.
    $status = $this->simple_mongo->remove($id, static::$collection);

    return $status;

  }
}
