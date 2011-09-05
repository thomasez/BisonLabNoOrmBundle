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

    /**
     * @var \RedpillLinpro\NosqlBundle\Services\ServiceInterface
     */
    protected $access_service;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $_reader = null;

    /**
     * @var \ReflectionClass
     */
    protected $_reflectedclass = null;
    
    protected $collection_resource;
    protected $entity_resource;
    protected $new_entity_resource;

    protected $model;
    protected $_id_property = null;
    protected $_id_column = null;

    /**
     * Get an Annotationn reader object
     *
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    public static function getAnnotationsReader()
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
    public function getReflectedClass()
    {
        if ($this->_reflectedclass === null) {
            $this->_reflectedclass = new \ReflectionClass($this->model);
        }
        return $this->_reflectedclass;
    }

    /**
     * This method is called internally from the class. It reads through the 
     * annotated properties to find which columns and resultset array keys is
     * defined as the identifier columns
     * 
     * This is needed for auto-populating object's id value for new objects, as
     * well as being able to return a proper array representation of the object
     * to the manager for storage.
     */
    protected function _populateAnnotatedIdValues()
    {
        if ($this->_id_column === null || $this->_id_property === null) {
            foreach ($this->getReflectedClass($this->model)->getProperties() as $property) {
                if ($id_annotation = $this->getIdAnnotation($property)) {
                    if (!$column_annotation = $this->getColumnAnnotation($property))
                        throw new Exception('You must set the Id annotation on a property annotated with @Column');

                    $this->_id_column = ($column_annotation->name) ? $column_annotation->name : $property->name;
                    $this->_id_property = $property->name;
                    break;
                }
            }
        }
    }
    
    /**
     * Returns the identifier column, used by the manager when finding which
     * data array column to use as the identifier value
     * 
     * @return string
     */
    public function getDataArrayIdentifierColumn()
    {
        $this->_populateAnnotatedIdValues();
        return $this->_id_column;
    }
    
    /**
     * Returns the identifier property, used by the entity when finding which
     * property to use as the identifier value
     * 
     * @return string
     */
    public function getDataArrayIdentifierProperty()
    {
        $this->_populateAnnotatedIdValues();
        return $this->_id_property;
    }
    
    public function getResourceRoute($routename)
    {
        if (!array_key_exists($routename, static::$resource_routes))
            throw new Exception('This route does not exist in the static array property $resource_routes on this manager');
                
        return static::$resource_routes[$routename];
    }
    
    public function __construct($access_service, $options = array())
    {
        $this->access_service = $access_service;
        if (array_key_exists('model', $options)) {
            $this->model = $options['model'];
        }
        if (array_key_exists('collection_resource', $options)) {
            $this->collection_resource = $options['collection_resource'];
        }
        if (array_key_exists('entity_resource', $options)) {
            $this->entity_resource = $options['entity_resource'];
        }
        if (array_key_exists('new_entity_resource', $options)) {
            $this->new_entity_resource = $options['new_entity_resource'];
        }
        if (!isset($this->new_entity_resource) || !isset($this->entity_resource) || !isset($this->collection_resource) || !isset($this->model)) {
            $rc = new \ReflectionClass(get_called_class());
            $resource_annotation = $this->getResourceAnnotation($rc);
            if ($resource_annotation instanceof \RedpillLinpro\NosqlBundle\Annotations\Resources) {
                if ($resource_annotation->collection) {
                    $this->collection_resource = $resource_annotation->collection;
                }
                if ($resource_annotation->entity) {
                    $this->entity_resource = $resource_annotation->entity;
                }
                if ($resource_annotation->new_entity) {
                    $this->new_entity_resource = $resource_annotation->new_entity;
                }
            }
            $model_annotation = $this->getModelAnnotation($rc);
            if ($model_annotation instanceof \RedpillLinpro\NosqlBundle\Annotations\Model) {
                if ($model_annotation->name) {
                    $this->model = $model_annotation->name;
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
    public function getResourceAnnotation($rc)
    {
        return self::getAnnotationsReader()->getClassAnnotation($rc, 'RedpillLinpro\\NosqlBundle\\Annotations\\Resources');
    }

    /**
     * Returns an Id annotation for a specified property if it exists
     * 
     * @param \ReflectionProperty $property
     * 
     * @return RedpillLinpro\NosqlBundle\Annotations\Id
     */
    public function getIdAnnotation($property)
    {
        return self::getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Id');
    }
    
    /**
     * Returns a Column annotation for a specified property if it exists
     * 
     * @param \ReflectionProperty $property
     * 
     * @return RedpillLinpro\NosqlBundle\Annotations\Column
     */
    public function getColumnAnnotation($property)
    {
        return self::getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Column');
    }
    
    /**
     * @return RedpillLinpro\NosqlBundle\Annotations\Model
     */
    public function getModelAnnotation($rc)
    {
        return self::getAnnotationsReader()->getClassAnnotation($rc, 'RedpillLinpro\\NosqlBundle\\Annotations\\Model');
    }

    public function getCollectionResource()
    {
        return $this->collection_resource;
    }

    public function getEntityResource()
    {
        return $this->entity_resource;
    }

    public function getNewEntityResource()
    {
        return $this->new_entity_resource;
    }

    public function getModelClassname()
    {
        return $this->model;
    }

    public function getInstantiatedModel()
    {
        $classname = $this->getModelClassname();
        return new $classname();
    }

    /*
     * Finders
     */

    public function findAll($params = array())
    {
        $objects = array();
        foreach ($this->access_service->findAll($this->getCollectionResource(), $params) as $o) {
            $object = $this->getInstantiatedModel();
            $object->fromDataArray($o, $this);
            $objects[] = $object;
        }

        return $objects;
    }

    public function findOneById($id, $params = array())
    {
        if (strpos($this->getEntityResource(), '{:'.$this->getDataArrayIdentifierColumn().'}') === false)
            throw new \Exception('This route does not have the required identification parameter, {'.$this->getDataArrayIdentifierColumn().'}');
                
        $resource = str_replace('{:'.$this->getDataArrayIdentifierColumn().'}', $id, $this->getEntityResource());
        $data = $this->access_service->findOneById(
                $resource, $id, $params);

        if (!$data) {
            return null;
        }

        $object = $this->getInstantiatedModel();
        $object->fromDataArray($data, $this);

        return $object;
    }

    public function findByKeyVal($key, $val, $params = array())
    {
        $objects = array();

        foreach ($this->access_service->findByKeyVal(
                $this->getCollectionResource(), $key, $val, $params) as $o) {
            $object = $this->getInstantiatedModel();
            $object->fromDataArray($o, $this);
            $objects[] = $object;
        }

        return $objects;
    }

    public function save($object)
    {
        $classname = $this->getModelClassname();
        if (!$object instanceof $classname) {
            throw new \InvalidArgumentException('This is not an object I can save, it must be of the same classname defined in this manager');
        }

        if (strpos($this->getEntityResource(), '{:'.$this->getDataArrayIdentifierColumn().'}') === false)
            throw new \Exception('This route does not have the required identification parameter, {'.$this->getDataArrayIdentifierColumn().'}');
        
        // Save can do both insert and update with MongoDB.
        if ($object->getDataArrayIdentifierValue()) {
            $resource = str_replace('{:'.$this->getDataArrayIdentifierColumn().'}', $object->getDataArrayIdentifierValue(), $this->getEntityResource());
        } else {
            $resource = $this->getNewEntityResource();
        }
        $new_data = $this->access_service->save($object, $resource);

        if (isset($new_data[$this->getDataArrayIdentifierColumn()])) {
            $object->setDataArrayIdentifierValue($new_data[$this->getDataArrayIdentifierColumn()]);
        }

        return $object;
    }

    public function delete($object)
    {

        $classname = $this->getModelClassname();
        if (!$object instanceof $classname) {
            throw new \InvalidArgumentException('This is not an object I can delete, it must be of the same classname defined in this manager');
        }

        if (!$object->getDataArrayIdentifierValue()) {
            throw new \InvalidArgumentException('This is not an object I can delete since it does not have a entity identifier value');
        }

        // Save can do both insert and update with MongoDB.
        $status = $this->access_service->remove($object->getDataArrayIdentifierValue(), $this->getEntityResource());

        return $status;
    }

}
