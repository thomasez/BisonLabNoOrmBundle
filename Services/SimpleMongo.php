<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace RedpillLinpro\NosqlBundle\Services;

class SimpleMongo
{

  private $mongo;
  private $mongodb;

  public function __construct($dbhost, $dbname, $dbuser)
  {
    $this->mongo = new \Mongo();
    $this->mongodb = $this->mongo->selectDB($dbname);
  }

  public function save($data, $collection = null)
  {
    if (is_object($data))
    {
      if (!$collection) 
      {
        $collection  = $data->getClassName();
      }
      $data = $data->toSimpleArray();
    }

    if (!$collection) 
    {
      throw new \InvalidArgumentException("Got no collection to save the data");
    }

    $mongo_collection = $this->mongodb->$collection;

    if (isset($data['id']))
    {
      $id = new \MongoId($data['id']);
      unset($data['id']);
      $mongo_collection->update(array('_id' => $id), $data);
    }
    else
    {
      $mongo_collection->insert($data);
    }
   
    $data['id']) = $data['_id'];
    unset($data['_id']);

    return $data; 

  }

  public function remove($data, $collection = null)
  {
    if (!$collection) 
    {
      throw new \InvalidArgumentException("Got no collection to delete");
    }

    if (is_object($data))
    {
      if (!$collection) 
      {
        $collection  = $data->getClassName();
      }
      $id = $data->getId();
    }
    else
    {
      $id = $data;
    }

    if (!$id) 
    {
      throw new \InvalidArgumentException("Got no id, cannot delete");
    }


    $mongo_collection = $this->mongodb->$collection;

    $mid = new \MongoId($id);
    $mongo_collection->remove(array('_id' => $mid), array('justOne' => true));
   
    // Should I return the data or a status of some sort? The data is kinda
    // supposed to be gone.
    return $data; 

  }

  public function findAll($collection, $params = array())
  {
    $retarr = array();

        // $this->mongodb->$collection->find() as $data)
    foreach (iterator_to_array($this->mongodb->$collection->find() as $data);
    {
      $data['id']) = $data['_id'];
      unset($data['_id']);
      $retarr[] = $data;
    }
    return $retarr;
  }

  public function findOneById($collection, $id)
  {
    return $this->mongodb->$collection->findOne(
        array('_id' => new \MongoId($id)));
  }

  public function findOneByKeyVal($collection, $key, $val)
  {
    return $this->mongodb->$collection->findOne(array($key => $val));
  }

  public function findByKeyVal($collection, $key, $val)
  {
    $retarr = array();

    // PHPs Mongodb thingie has an issue with numbers, it quotes them 
    // unless it is explocitly typecasted or manipulated in math context.
    if (is_numeric($val))
    {
      $val = $val * 1;
    }

    $cursor = $this->mongodb->$collection->find(array($key => $val));
    // Since I am cooking rigth from php.net I'll use while here:
    while ($cursor->hasNext())
    {
      $data = $cursor->getNext();
      $retarr[] = $data;
    }
    return $retarr;

  }

}
