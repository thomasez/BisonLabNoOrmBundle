<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace BisonLab\NoOrmBundle\Manager;

abstract class BaseManager
{
    /* 
     * Remember to put these in the Manager extending this one.
     * Right now they are all the same but I define different names here.
     * Or rather, they have to be defined in the object extending this one.
     */
    // protected static $_collection  = 'Base';
    // protected static $_model       = 'Model\Base';
    // And if you need it;
    // protected static $text_search_fields = ["Template"];

    protected $access_service;
    protected $options;

    public function __construct($access_service, $options = array())
    {
        $this->access_service = $access_service;
        // Not sure I use this at all and must not be confused with options
        // sent to each function.
        $this->options = $options;
       /*
        if (array_key_exists('model', $options)) {
            $this->model = $options['model'];
        }
        */
    }

    public function getConnection()
    {
        return $this->access_service->getConnection();
    }

    /*
     * Finders
     */
    public function findAll($options = array())
    {
        $objects = array();
        foreach ($this->access_service->findAll(
            static::$_collection, $options) as $o) {
          $object = new static::$_model($o);
          $object->setId($o[$object->getIdKey()]);
          $objects[] = $object;
        }
        return $objects;
    }

    /*
     * Personally I'd lke to use get for single and find for multiple, but as
     * long as so many others (at least Doctrine..) uses find and findOne I'll
     * stick to it aswell. 
     */
    public function findOneById($id)
    {
        // This was kinda annoying and I'm sure I just doing it wrong.
        $m = static::$_model;
        $data = $this->access_service->findOneById(
            static::$_collection, $m::getIdKey(), $id);
      
        if (!$data) {
            return null;
        }
      
        $object = new static::$_model($data);
        return $object;
    }

    /*
     * Options, why not?
     * For now I would like these:
     *  - orderBy =  array($key => [ASC|DESC])
     *  - orderBy =  array( array($key => [ASC|DESC]), array($key => [ASC|DESC]) )
     *  - limit
     *
     * For the adaptors, implement what you are able to.
     */
    public function findOneByKeyVal($key, $val, $options = array())
    {
        $this->_handleOptions($options);
      
        $objects = array();
        $data = $this->access_service->findOneByKeyVal(
                        static::$_collection, $key, $val, $options);
        if (!$data) {
            return null;
        }
        $object = new static::$_model($data);
        return $object;
    }

    /* 
     * Increasingly non-simple.
     */
    public function findByKeyVal($key, $val, $options = array())
    {
        $this->_handleOptions($options);
        $objects = array();
      
        foreach ($this->access_service->findByKeyVal(
            static::$_collection, $key, $val, $options) as $data) {
          $object = new static::$_model($data);
          $objects[] = $object;
        }
        return $objects;
    }

    /*
     * Odd name? Basically, this ANDs all elements in the criteria array().
     */
    public function findOneByKeyValAndSet($criteria = array(), $options = array())
    {
        $this->_handleOptions($options);
        $objects = array();
        $data = $this->access_service->findOneByKeyValAndSet(
                        static::$_collection, $criteria, $options);
      
        if (!$data) {
          return null;
        }
      
        $object = new static::$_model($data);
        return $object;
    }

    /*
     * Odd name? Basically, this ANDs all elements in the criteria array().
     */
    public function findByKeyValAndSet($criteria = array(), $options = array())
    {
        $this->_handleOptions($options);
        $objects = array();
      
        foreach ($this->access_service->findByKeyValAndSet(
            static::$_collection, $criteria, $options) as $data) {
          $object = new static::$_model($data);
          $objects[] = $object;
        }
        return $objects;
    }

    /*
     * Or is it really simple? It's meant to know fields to search for aswell.
     */
    public function simpleTextSearch($text, $options = array())
    {
        $this->_handleOptions($options);
        $objects = array();
      
        foreach ($this->access_service->simpleTextSearch(
            static::$_collection, static::$text_search_fields,
                $text, $options) as $data) {
          $object = new static::$_model($data);
          $objects[] = $object;
        }
        return $objects;
    }

    /*
     * Save can do both insert and update with MongoDB.
     * Other adapters can do it however they like and still support this save.
     * UPSERT, REPLACE INTO, get/post and so on.
     */
    public function save($object)
    {
        $new_data = $this->access_service->save($object, static::$_collection);
      
        if (isset($new_data[$object->getIdKey()])) {
          $object->setId($new_data[$object->getIdKey()]);
        }
        return $object;
    }

    /*
     * Why is this not called remove? Good question.
     */
    public function delete($object)
    {
        if (is_object($object) && $id = $object->getId()) {
            // This could be discussed as being superfluous or not, since later
            // here I'll just accept the object as being an id and just delete
            // it..  So why bother checking this then?
            if ($object->getClassName() != static::$_collection
                && get_class($object) != static::$_model) {
                throw new \InvalidArgumentException('This is not an object I can delete. It may be a missing Id or wrong class.');
            }
        } elseif (!is_object($object)) {
           $id = $object; 
        }
      
        if (!$id) {
            throw new \InvalidArgumentException('This is not an object I can delete. It may be a missing Id or wrong class.');
        }
        $status = $this->access_service->remove($id, static::$_collection);
        return $status;
    }

    /*
     * Which is why we alias.
     */
    public function remove($object)
    {
        return $this->delete($object);
    }

    /*
     * Helper functions.
     */
    private function _handleOptions(&$options)
    {
        // I want the adapters as simple as posible, which menas I always send
        // them multiple orderBy while letting the users of the Bundle to do
        // whatever.
        if (isset($options['orderBy'])) {
            // Array of orderBy's? If not, we have to create it.
            if (!is_array($options['orderBy'][0])) {
                $options['orderBy'] = array($options['orderBy']);
            }
        }
    }
}
