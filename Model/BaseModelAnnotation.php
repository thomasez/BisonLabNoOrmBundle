<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Model;

abstract class BaseModelAnnotation implements StorableObjectInterface
{

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected static $_reader = null;

    /**
     * @var \RedpillLinpro\NosqlBundle\Manager\BaseManager
     */
    protected $_entitymanager = null;
    
    protected $_resource_location = null;
    protected $_resource_location_prefix = null;

    /**
     * Called from the manager, populates the object with data from a response
     * Also passes the manager in, so a reference to it can be statically cached
     * for later calls (lazy-loading / auto-retrieving), etc.
     * 
     * @param array $data
     * @param \RedpillLinpro\NosqlBundle\Manager\BaseManager $manager 
     */
    public function fromDataArray($data, \RedpillLinpro\NosqlBundle\Manager\BaseManager $manager)
    {
        $this->_entitymanager = $manager;
        $this->_dataArrayMap($data);
    }

    /**
     * The manager calls this method to retrieve an array representation of the
     * object data, based on the structure defined in the object's annotations
     * 
     * @return array
     */
    public function toDataArray()
    {
        return $this->_extractToDataArray();
    }

    /**
     * Returns the unique identifier value for this object, usually the value
     * of an $id property, $<objecttype>Id or similar
     * 
     * @return mixed
     */
    public function getDataArrayIdentifierValue()
    {
        return $this->{$this->_entitymanager->getDataArrayIdentifierProperty()};
    }
    
    public function hasDataArrayIdentifierValue()
    {
        return $this->_entitymanager->hasDataArrayIdentifierProperty();
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
        $property = $this->_entitymanager->getDataArrayIdentifierColumn();
        $this->$property = $identifier_value;
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
        return $this->_entitymanager->getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Id');
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
        return $this->_entitymanager->getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Column');
    }

    /**
     * Returns a Relates annotation for a specified property if it exists
     * 
     * @param \ReflectionProperty $property
     * 
     * @return RedpillLinpro\NosqlBundle\Annotations\Relates
     */
    public function getRelatesAnnotation($property)
    {
        return $this->_entitymanager->getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Relates');
    }
    
    public function setResourceLocationPrefix($rlp)
    {
        $this->_resource_location_prefix = $rlp;
    }

    /**
     * Returns the resource location for this object, used when saving this
     * object via the manager
     * 
     * @return string
     */
    protected function _getResourceLocation()
    {
        if ($this->_resource_location === null) {
            $this->_resource_location = str_replace('{:'.$this->_entitymanager->getDataArrayIdentifierColumn().'}', $this->{$this->_entitymanager->getDataArrayIdentifierProperty()}, $this->_entitymanager->getEntityResource());
        }
        return $this->_resource_location_prefix . $this->_resource_location;
    }
    
    protected function getResourceByRoutename($routename, $params = array())
    {
        $resource = $this->_entitymanager->getResourceRoute($routename);
        foreach ($params as $key => $value) {
            $resource = str_replace("{:{$key}}", $value, $resource);
        }
        return $resource;
    }
    
    protected function _apiCall($routename, $params = array())
    {
        return $this->_apiGet($routename, $params);
    }
    
    protected function _apiGet($routename, $params = array())
    {
        $resource_route = $this->getResourceByRoutename($routename, $params);
        $resource_route = (substr($resource_route, 0, 1) == "/") ? $resource_route : $this->_getResourceLocation() . '/' . $resource_route;
        
        return $this->_entitymanager->getAccessService()->call($resource_route);
    }
    
    protected function _apiSet($routename, $params = array(), $post_params = array())
    {
        $resource_route = $this->getResourceByRoutename($routename, $params);
        $resource_route = (substr($resource_route, 0, 1) == "/") ? $resource_route : $this->_getResourceLocation() . '/' . $resource_route;
        
        return $this->_entitymanager->getAccessService()->call($resource_route, 'POST', $post_params);
    }
    
    protected function _apiUnset($routename, $params = array(), $post_params = array())
    {
        $resource_route = $this->getResourceByRoutename($routename, $params);
        $resource_route = (substr($resource_route, 0, 1) == "/") ? $resource_route : $this->_getResourceLocation() . '/' . $resource_route;
        
        return $this->_entitymanager->getAccessService()->call($resource_route, 'DELETE', $post_params);
    }
    
    protected function _applyDataArrayProperty($property, $result, $extracted = null)
    {
        $id_annotation = ($this->_entitymanager->getDataArrayIdentifierProperty() !== null || $extracted !== null) ? null : $this->getIdAnnotation($property);
        $column_annotation = ($extracted !== null) ? null : $this->getColumnAnnotation($property);
        $relates_annotation = ($extracted !== null) ? null : $this->getRelatesAnnotation($property);

        if ($column_annotation !== null || $extracted !== null) {
            if ($extracted !== null) {
                if (array_key_exists($extracted, $result)) {
                    $this->$property = $result[$extracted];
                }
            } else {
                $name = ($column_annotation->name) ? $column_annotation->name : $property->name;
                if ($extract_annotation = $this->_entitymanager->getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Extract')) {

                    if (!$extract_annotation->hasColumns())
                        throw new \Exception('No columns defined for the extract annotation');

                    foreach ($extract_annotation->columns as $column => $extract_to_property) {
                        $this->_applyDataArrayProperty($extract_to_property, $result[$name], $column);
                    }
                } elseif (array_key_exists($name, $result)) {
                    if ($relates_annotation !== null && is_array($result[$name])) {
                        if ($relates_annotation->manager) {
                            $related_manager = $relates_annotation->manager;
                            $related_manager = new $related_manager($this->_entitymanager->getAccessService());
                        } else {
                            $related_manager = null;
                        }
                        $this->_mapRelationData($property->name, $result[$name], $relates_annotation, $related_manager);
                    } else {
                        $this->{$property->name} = $result[$name];
                    }
                }
            }
        }
    }
    
    protected function _populateRelatedObject($property)
    {
        if (is_array($this->$property) || is_object($this->$property)) return;
        
        $reflected_property = $this->_entitymanager->getReflectedClass()->getProperty($property);
        $relates_annotation = $this->getRelatesAnnotation($reflected_property);
        $related_manager = $relates_annotation->manager;
        $related_manager = new $related_manager($this->_entitymanager->getAccessService());
        if (!$related_manager instanceof \RedpillLinpro\NosqlBundle\Manager\BaseManager)
            throw new \Exception('The manager object must extend the nosql base manager class. Check that the manager= annotation has been correctly defined');
            
        if (is_numeric($this->$property)) {
            $related_resource_location = str_replace('{:'.$related_manager->getDataArrayIdentifierColumn().'}', $this->$property, $relates_annotation->resource);
        } else {
            $related_resource_location = $relates_annotation->resource;
        }
        $final_resource_location = (substr($related_resource_location, 0, 1) == "/") ? $related_resource_location : $this->_getResourceLocation() . '/' . $related_resource_location;
        $data = $this->_entitymanager->getAccessService()->call($final_resource_location, 'GET', array());
        
        $this->_mapRelationData($property, $data, $relates_annotation, $related_manager);
    }
    
    protected function _mapRelationData($property, $data, \RedpillLinpro\NosqlBundle\Annotations\Relates $relates_annotation, $manager = null)
    {
        if (!$manager) {
            $value = $data;
        } elseif ($relates_annotation->collection) {
            $value = array();
            foreach ($data as $single_result) {
                $object = $manager->getInstantiatedModel();
                $object->fromDataArray($single_result, $manager);
                $object->setResourceLocationPrefix($this->_getResourceLocation() . "/");
                if ($object->hasDataArrayIdentifierValue()) {
                    $value[$object->getDataArrayIdentifierValue()] = $object;
                } else {
                    $value[] = $object;
                }
            }
        } else {
            $value = $manager->getInstantiatedModel();
            $value->fromDataArray($data, $manager);
            $value->setResourceLocationPrefix($this->_getResourceLocation() . "/");
        }
        
        $this->$property = $value;
    }

    protected function _extractDataArrayProperty($property, &$result, $extracted = null)
    {
        $column_annotation = ($extracted !== null) ? null : $this->_entitymanager->getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Column');

        if ($column_annotation !== null || $extracted !== null) {
            if ($extracted !== null) {
                $result[$extracted] = $this->$property;
            } else {
                $name = ($column_annotation->name) ? $column_annotation->name : $property->name;
                if ($extract_annotation = $this->_entitymanager->getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Extract')) {
                    $return_value = array();
                    foreach ($extract_annotation->columns as $column => $extract_from_property) {
                        $this->_extractDataArrayProperty($extract_from_property, $return_value, $column);
                    }
                    $result[$name] = $return_value;
                } else {
                    $result[$name] = $this->{$property->name};
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
    protected function _dataArrayMap($result)
    {
        foreach ($this->_entitymanager->getReflectedClass()->getProperties() as $property) {
            $this->_applyDataArrayProperty($property, $result);
        }
    }

    protected function _extractToDataArray()
    {
        $result = array();

        foreach ($this->_entitymanager->getReflectedClass()->getProperties() as $property) {
            $this->_extractDataArrayProperty($property, $result);
        }

        return $result;
    }

    static function getFormSetup()
    {
        return static::$model_setup;
    }

    static function getClassName()
    {
        return 'user';
    }

}

