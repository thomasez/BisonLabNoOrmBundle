<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011+ Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Services;

/* The increasingly innacurately names */
class SimpleMongo implements ServiceInterface
{

    private $mongo;
    private $mongodb;

    public function __construct($dbhost, $dbname, $dbuser)
    {
        $this->mongo = new \MongoClient();
        $this->mongodb = $this->mongo->selectDB($dbname);
    }

    public function save($data, $collection = null)
    {
        if (is_object($data)) {
            $data = $data->toCompleteArray();
        }

        if (!$collection) {
            throw new \InvalidArgumentException("Got no collection to save the data");
        }

        $mongo_collection = $this->mongodb->$collection;

        if (isset($data['id']))
        {
            $mongo_id = new \MongoId($data['id']);
            // Back 
            unset($data['id']);
            $mongo_collection->update(array('_id' => $mongo_id), $data);
            // and Forth
            $data['id'] = $mongo_id->{'$id'};
            unset($data['_id']);
        }
        else
        {
            $mongo_collection->insert($data);
            $data['id'] = $data['_id']->{'$id'};
            unset($data['_id']);
        }

        return $data;
    }

    public function remove($data, $collection = null)
    {
        if (!$collection) {
            throw new \InvalidArgumentException("Got no collection to delete");
        }

        if (is_object($data)) {
            $id = $data->getId();
        } else {
            $id = $data;
        }

        $mongo_collection = $this->mongodb->$collection;

        $mid = new \MongoId($id);
        return $mongo_collection->remove(array('_id' => $mid), 
                array('justOne' => true));
    }

    public function findAll($collection, $options = array())
    {
        $retarr = array();

        foreach (iterator_to_array($this->mongodb->$collection->find()) 
                    as $data) {

            $data['id'] = $data['_id'];
            unset($data['_id']);
            $retarr[] = $data;

        }
        return $retarr;
    }
    
    public function findOneById($collection, $id_key, $id, $options = array())
    {

        // Not sure if this is the right way or if I should throw an 
        // exception. But since I dislike exceptions....
        if (empty($id)) { return null; }

        $data = $this->mongodb->$collection->findOne(
           array('_id' => new \MongoId($id)));

        if (is_null($data)) { return null; }

        $data['id'] = $data['_id']->{'$id'};
        unset($data['_id']);
        return $data;
    }
    
    public function findOneByKeyVal($collection, $key, $val, $options = array())
    {
        $data = $this->mongodb->$collection->findOne(array($key => $val));

        if (is_null($data)) { return null; }

        $data['id'] = $data['_id']->{'$id'};
        unset($data['_id']);
        return $data;
    }
    
    public function findByKeyVal($collection, $key, $val, $options = array())
    {
        $retarr = array();
    
        // PHPs Mongodb thingie has an issue with numbers, it quotes them 
        // unless it is explocitly typecasted or manipulated in math context.
        if (is_numeric($val)) {
            $val = $val * 1;
        }
    
        $cursor = $this->mongodb->$collection->find(array($key => $val));
        $this->_handleOptions($cursor, $options);

        // Since I am cooking rigth from php.net I'll use while here:
        while ($cursor->hasNext()) {
            $data = $cursor->getNext();
            $data['id'] = $data['_id'];
            unset($data['_id']);
            $retarr[] = $data;
        }
        return $retarr;
    }

    public function findOneByKeyValAndSet($collection, $criterias, $options = array())
    {
        /* According to https://blog.serverdensity.com/checking-if-a-document-exists-mongodb-slow-findone-vs-find/
         * using find and the cursor is a lot faster..
         */
        // $data = $this->mongodb->$collection->findOne($criterias);
        $cursor = $this->mongodb->$collection->find($criterias);
        $this->_handleOptions($cursor, $options);

        $data = null;
        if ($cursor->hasNext())
            $data = $cursor->getNext();

        if (is_null($data)) { return null; }

        $data['id'] = $data['_id'];
        unset($data['_id']);
        return $data;
    }
    
    public function findByKeyValAndSet($collection, $criterias, $options = array())
    {
        $retarr = array();
    
        foreach ($criterias as $key => $val) {
            // PHPs Mongodb thingie has an issue with numbers, it quotes them 
            // unless it is explocitly typecasted or manipulated in math context.
            if (is_numeric($val)) {
                $criterias[$val] = $val * 1;
            }
        }
    
        $cursor = $this->mongodb->$collection->find($criterias);
        $this->_handleOptions($cursor, $options);

        // Since I am cooking rigth from php.net I'll use while here:
        while ($cursor->hasNext()) {
            $data = $cursor->getNext();
            $data['id'] = $data['_id'];
            unset($data['_id']);
            $retarr[] = $data;
        }
        return $retarr;
    }

    private function _handleOptions(&$cursor, &$options)
    {
        if (isset($options['orderBy'])) {
            $sort = array();
            foreach ($options['orderBy'] as $orderBy) {
                $order = $orderBy[1] == "ASC" ? 1 : -1;
                $cursor->sort(array($orderBy[0] => $order));
            }
        }

        if (isset($options['limit'])) {
            $cursor->limit($options['limit']);
        }
    }
}
