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

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $_reader = null;
    
    /**
     * Get an Annotationn reader object
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    protected static function getAnnotationsReader()
    {
        if (self::$_reader === null)
        {
            self::$_reader = new \Doctrine\Common\Annotations\AnnotationReader(new \Doctrine\Common\Cache\ArrayCache());
            self::$_reader->setEnableParsePhpImports(true);
            self::$_reader->setDefaultAnnotationNamespace('\\Schibsted\\AmbassadorBundle\\Model\\Annotations\\');
        }
        return self::$_reader;
    }

  
  public function __construct($access_service)
  {
    $this->access_service = $access_service;
  }

    protected static function _applyProperty($class, $object, $property, $result, $extracted = null)
    {
        $column_annotation = ($extracted !== null) ? null : self::getAnnotationsReader()->getPropertyAnnotation($property, '\\Schibsted\\AmbassadorBundle\\Model\\Annotations\\Column');
        if ($column_annotation !== null || $extracted !== null) {
            if ($extracted !== null) {
                if (array_key_exists($extracted, $result)) {
                    $function_name = 'set'.ucfirst($property);
                    $object->$function_name($result[$extracted]);
                }
            } else {
                $name = ($column_annotation->name) ? $column_annotation->name : $property->name;
                if ($extract_annotation = self::getAnnotationsReader()->getPropertyAnnotation($property, '\\Schibsted\\AmbassadorBundle\\Model\\Annotations\\Extract')) {
                    foreach ($extract_annotation->columns as $column => $extract_to_property) {
                        static::_applyProperty($class, $object, $extract_to_property, $result[$name], $column);
                    }
                } elseif (array_key_exists($name, $result)) {
                    $function_name = 'set'.ucfirst($property->name);
                    $object->$function_name($result[$name]);
                }
            }
        }
    }

    /**
     * Convert the result arrays from VGS client to collection of Entities
     *
     * @param array $resultset array of responses from VGS_Client
     * @return array
     */
    protected static function map($result, $object)
    {
        $class = new \ReflectionClass(static::$model);
        foreach ($class->getProperties() as $property) {
            static::_applyProperty($class, $object, $property, $result);
        }
    }
  
  /*
   * Finders
   */
  public function findAll($params = array())
  {

    $objects = array();
    foreach ($this->access_service->findAll(static::$collection, $params) as $o)
    {
      $object = new static::$model();
      static::map($o, $object);
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

    $object = new static::$model();
    static::map($data, $object);

    return $object;
  }

  public function findByKeyVal($key, $val)
  {
    $objects = array();

    foreach ($this->access_service->findByKeyVal(
        static::$collection, $key, $val) as $o)
    {
      $object = new static::$model();
      static::map($o, $object);
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
