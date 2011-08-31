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
    protected static $model;
    protected static $collection_resource;
    protected static $entity_resource;
    protected static $new_entity_resource;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $_reader = null;

    /**
     * @var \ReflectionClass
     */
    protected static $_reflectedclass = null;

    /**
     * Get an Annotationn reader object
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    protected static function getAnnotationsReader()
    {
        if (self::$_reader === null) {
            self::$_reader = new \Doctrine\Common\Annotations\AnnotationReader(new \Doctrine\Common\Cache\ArrayCache());
            self::$_reader->setEnableParsePhpImports(true);
            self::$_reader->setDefaultAnnotationNamespace('RedpillLinpro\\NosqlBundle\\Annotations\\');
        }
        return self::$_reader;
    }
    
    /**
     * Get a reflection class object valid for this static class, so we don't
     * have to instantiate a new one for each instance with the overhead that
     * comes with it
     * 
     * @return \ReflectionClass
     */
    protected static function getReflectedClass()
    {
        if (static::$_reflectedclass === null) {
            static::$_reflectedclass = new \ReflectionClass(get_called_class());
        }
        return static::$_reflectedclass;
    }

    public function __construct($access_service, $options = array())
    {
        $this->access_service = $access_service;
        if (array_key_exists('model', $options)) {
            static::$model = $options['model'];
        }
        if (array_key_exists('collection_resource', $options)) {
            static::$collection_resource = $options['collection_resource'];
        }
        if (array_key_exists('entity_resource', $options)) {
            static::$entity_resource = $options['entity_resource'];
        }
        if (array_key_exists('new_entity_resource', $options)) {
            static::$new_entity_resource = $options['new_entity_resource'];
        }
        if (!isset(static::$new_entity_resource) || !isset(static::$entity_resource) || !isset(static::$collection_resource) || !isset(static::$model)) {
            $resource_annotation = static::getResourceAnnotation();
            if ($resource_annotation instanceof \RedpillLinpro\NosqlBundle\Annotations\Resources) {
                if ($resource_annotation->collection) {
                    static::$collection_resource = $resource_annotation->collection;
                }
                if ($resource_annotation->entity) {
                    static::$entity_resource = $resource_annotation->entity;
                }
                if ($resource_annotation->new_entity) {
                    static::$new_entity_resource = $resource_annotation->new_entity;
                }
            }
            $model_annotation = static::getModelAnnotation();
            if ($model_annotation instanceof \RedpillLinpro\NosqlBundle\Annotations\Model) {
                if ($model_annotation->name) {
                    static::$model = $model_annotation->name;
                }
            }
        }
    }
    
    /**
     * @return \RedpillLinpro\NosqlBundle\Services\ServiceInterface
     */
    public function getAccessService()
    {
        return $this->access_service;
    }

    /**
     * @return RedpillLinpro\NosqlBundle\Annotations\Resources
     */
    public static function getResourceAnnotation()
    {
        return self::getAnnotationsReader()->getClassAnnotation(static::getReflectedClass(), 'RedpillLinpro\\NosqlBundle\\Annotations\\Resources');
    }

    /**
     * @return RedpillLinpro\NosqlBundle\Annotations\Model
     */
    public static function getModelAnnotation()
    {
        return self::getAnnotationsReader()->getClassAnnotation(static::getReflectedClass(), 'RedpillLinpro\\NosqlBundle\\Annotations\\Model');
    }

    public static function getCollectionResource()
    {
        return static::$collection_resource;
    }

    public static function getEntityResource()
    {
        return static::$entity_resource;
    }

    public static function getNewEntityResource()
    {
        return static::$new_entity_resource;
    }

    public static function getModelClassname()
    {
        return static::$model;
    }

    public static function getInstantiatedModel()
    {
        $classname = static::getModelClassname();
        return new $classname();
    }

    /*
     * Finders
     */

    public function findAll($params = array())
    {
        $objects = array();
        foreach ($this->access_service->findAll(static::getCollectionResource(), $params) as $o) {
            $object = static::getInstantiatedModel();
            $object->fromDataArray($o, $this);
            $objects[] = $object;
        }

        return $objects;
    }

    public function findOneById($id, $params = array())
    {
        $resource = str_replace(':id', $id, static::getEntityResource());
        $data = $this->access_service->findOneById(
                $resource, $id, $params);

        if (!$data) {
            return null;
        }

        $object = static::getInstantiatedModel();
        $object->fromDataArray($data, $this);

        return $object;
    }

    public function findByKeyVal($key, $val, $params = array())
    {
        $objects = array();

        foreach ($this->access_service->findByKeyVal(
                static::getCollectionResource(), $key, $val, $params) as $o) {
            $object = static::getInstantiatedModel();
            $object->fromDataArray($o, $this);
            $objects[] = $object;
        }

        return $objects;
    }

    public function save($object)
    {
        $classname = static::getModelClassname();
        if (!$object instanceof $classname) {
            throw new \InvalidArgumentException('This is not an object I can save, it must be of the same classname defined in this manager');
        }

        // Save can do both insert and update with MongoDB.
        if ($object->getDataArrayIdentifierValue()) {
            $resource = str_replace(':id', $object->getDataArrayIdentifierValue(), static::getEntityResource());
        } else {
            $resource = static::getNewEntityResource();
        }
        $new_data = $this->access_service->save($object, $resource);

        if (isset($new_data[$object->getDataArrayIdentifierColumn()])) {
            $object->setId($new_data[$object->getDataArrayIdentifierColumn()]);
        }

        return $object;
    }

    public function delete($object)
    {

        $classname = static::getModelClassname();
        if (!$object instanceof $classname) {
            throw new \InvalidArgumentException('This is not an object I can delete, it must be of the same classname defined in this manager');
        }

        if (!$object->getDataArrayIdentifierValue()) {
            throw new \InvalidArgumentException('This is not an object I can delete since it does not have a entity identifier value');
        }

        // Save can do both insert and update with MongoDB.
        $status = $this->access_service->remove($object->getDataArrayIdentifierValue(), static::getEntityResource());

        return $status;
    }

}
