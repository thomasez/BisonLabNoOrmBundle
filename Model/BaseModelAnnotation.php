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

    public function fromDataArray($data = array())
    {
        $this->_dataArrayMap($data);
    }

    public function toDataArray()
    {

    }

    /*
     * Statics.
     */

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

    protected function _applyDataArrayProperty($property, $result, $extracted = null)
    {
        $column_annotation = ($extracted !== null) ? null : self::getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Column');
        if ($column_annotation !== null || $extracted !== null) {
            if ($extracted !== null) {
                if (array_key_exists($extracted, $result)) {
                    $this->$property = $result[$extracted];
                }
            } else {
                $name = ($column_annotation->name) ? $column_annotation->name : $property->name;
                if ($extract_annotation = self::getAnnotationsReader()->getPropertyAnnotation($property, 'RedpillLinpro\\NosqlBundle\\Annotations\\Extract')) {
                    $columns = ($extract_annotation->hasColumns()) ? $extract_annotation->columns : array_keys($result[$name]);
                    foreach ($columns as $column => $extract_to_property) {
                        $this->_applyDataArrayProperty($extract_to_property, $result[$name], $column);
                    }
                } elseif (array_key_exists($name, $result)) {
                    $this->{$property->name} = $result[$name];
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
        $reflection_class = static::getReflectedClass();
        foreach ($reflection_class->getProperties() as $property) {
            $this->_applyDataArrayProperty($property, $result);
        }
    }

    static function getFormSetup()
    {
      return static::$model_setup;
    }

    static function getClassName()
    {
      return static::$classname;
    }

}

