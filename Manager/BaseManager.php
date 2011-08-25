<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace RedpillLinpro\NosqlBundle\Manager;

abstract class BaseManager
{
  /* 
   * Remember to put these in the Manages extending this one.
   * Right now they are all the same but I define different names here.
   * Or rather, they have to be defined in the object extending this one.
   */
  // protected static $collection  = 'Base';
  // protected static $model       = 'Model\Base';

  protected $access_service;

  public function __construct($access_service)
  {
    $this->access_service = $access_service;
  }

  /*
   * Finders
   */
  public function findAll($params = array())
  {

    $objects = array();
    foreach ($this->access_service->findAll(static::$collection, $params) as $o)
    {
      $object = new static::$model($o);
      $object->setId($o['id']);
      $objects[] = $object;
    }

    return $objects;

  }

  public function findOneById($id)
  {
    $data = $this->access_service->findOneById(
        static::$collection, $id);

    if (!$data)
    {
      return null;
    }

    $object = new static::$model($data);

    return $object;
  }

  public function findByKeyVal($key, $val)
  {
    $objects = array();

    foreach ($this->access_service->findByKeyVal(
        static::$collection, $key, $val) as $o)
    {
      $object = new static::$model($data);
      $objects[] = $object;
    }

    return $objects;
  }

  public function save($object)
  {
    if ($object->getClassName() != static::$collection)
    {
      throw new \InvalidArgumentException('This is not an object I can save');
    }

    // Save can do both insert and update with MongoDB.
    $new_data = $this->access_service->save($object, static::$collection);

    if (isset($new_data['id']))
    {
      $object->setId($new_data['id']);
    }

    return $object;

  }

  public function delete($object)
  {

    if (is_object($object))
    {
      if ($object->getClassName() != static::$collection)
      {
        throw new \InvalidArgumentException('This is not an object I can delete');
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

    if (empty($id))
    {
      throw new \InvalidArgumentException('This is not an object I can delete since I do not have a unique identifier which right now is "id"');
    }

    // Save can do both insert and update with MongoDB.
    $status = $this->access_service->remove($id, static::$collection);

    return $status;

  }
}
