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
        self::map($data);
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

    static function getFormSetup()
    {
      return static::$model_setup;
    }

    static function getClassName()
    {
      return static::$classname;
    }

}

