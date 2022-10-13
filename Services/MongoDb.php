<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011+ Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

use MongoDB\Driver\Query;
use MongoDB\Driver\Manager;
use MongoDB\Collection;
use MongoDB\Operation\FindOneAndReplace;
use MongoDB\BSON\ObjectID as ObjectID;

class MongoDb implements ServiceInterface
{
    private $mongodb_manager;
    private $dbname;

    /* 
     * TODO: Move to named arguments.
     */
    public function __construct($dbhost = 'localhost', $dbname, $dbuser, $dbpass, $dbport = 27017)
    {
        $up = '';
        if ($dbuser) {
            $up = $dbuser;
            if ($dbpass)
                $up .= ':' . $dbpass;
            $up .= '@';
        }
        $uri = 'mongodb://' . $dbhost.':' . $dbport . '/' . $dbname;
        $this->mongodb_manager = new \MongoDB\Driver\Manager($uri);
        $this->dbname = $dbname;
    }

    public function getConnection()
    {
        return $this->mongodb_manager;
    }

    public function save($data, $collection = null)
    {
        $mongo_collection = $this->_getMongoCollection($collection);
        if (is_object($data)) {
            $data = $data->toCompleteArray();
        }

        $object_id = null;
        if (isset($data['id'])) {
            $object_id = new ObjectId($data['id']);
        } 

        /* Good thing this one works. */
/*      
        $result = $mongo_collection->findOneAndReplace(
            array('_id' => $object_id), 
            $data, 
            array(
                'upsert' => true,
                'returnDocument' => FindOneAndReplace::RETURN_DOCUMENT_AFTER
                )
            );
        $this->_convertStdClass($result);
        return $result;
*/

        // This triggers a bug somewhere in the MongoDB\Driver.
        if (isset($data['id'])) {
            $object_id = new ObjectId($data['id']);
            // Back 
            unset($data['id']);
            $mongo_collection->replaceOne(array('_id' => $object_id), $data);
            // and Forth
            $data['id'] = (string)$object_id;
            unset($data['_id']);
        } else {
            $result = $mongo_collection->insertOne($data);
            $data =  $this->_convertStdClass($result, $result->getInsertedId());
        }
        return $data;
    }

    public function remove($data, $collection = null)
    {
        $mongo_collection = $this->_getMongoCollection($collection);

        if (is_object($data)) {
            $id = $data->getId();
        } else {
            $id = $data;
        }

        $oid = new ObjectId($id);
        return $mongo_collection->deleteOne(array('_id' => $oid));
    }

    public function findAll($collection, $options = array())
    {
        return $this->findByKeyValAndSet($collection, array(), $options);
    }
    
    public function findOneById($collection, $id_key, $id, $options = array())
    {
        $mongo_collection = $this->_getMongoCollection($collection);

        // Not sure if this is the right way or if I should throw an 
        // exception. But since I dislike exceptions. (Yes, I am using them..)
        if (empty($id)) { return null; }

        return $this->findOneByKeyValAndSet($collection, array('_id' => new ObjectId($id)), $options);
    }
    
    public function findByKeyVal($collection, $key, $val, $options = array())
    {
        return $this->findByKeyValAndSet($collection,
                array($key => $val), $options);
    }

    public function findOneByKeyVal($collection, $key, $val, $options = array())
    {
        return $this->findOneByKeyValAndSet($collection,
                array($key => $val), $options);
    }
    
    public function findOneByKeyValAndSet($collection, $criterias, $options = array())
    {
        $this->_handleOptions($options);
        $mongo_collection = $this->_getMongoCollection($collection);

        // I used to have find($criterias) here, and then pick the first one,
        // but since Mongo does have a findOne, I'll rather use that.
        $data = $mongo_collection->findOne($criterias, $options);
        if (is_null($data)) { return null; }

        return $this->_convertStdClass($data);
    }
    
    public function findByKeyValAndSet($collection, $criterias, $options = array())
    {
        $this->_handleOptions($options);
        $mongo_collection = $this->_getMongoCollection($collection);
    
        foreach ($criterias as $key => $val) {
            // PHPs Mongodb thingie has an issue with numbers, it quotes them 
            // unless it is explocitly typecasted or manipulated in math context.
            if (is_numeric($val)) {
                $criterias[$key] = $val * 1;
            }
        }
    
        $cursor = $mongo_collection->find($criterias, $options);

        $retarr = array();
        foreach ($cursor as $data) {
            $this->_convertStdClass($data);
            $retarr[] = $data;
        }
        return $retarr;
    }
    
    /*
     * Very simple, until I need more advanced features.
     * Fields is not used in mongodb, you gotta define which ones is included
     * in text searches upfront. (As far as I understood it)
     * https://docs.mongodb.com/manual/text-search/
     */ 
    public function simpleTextSearch($collection, $fields, $text, $options = array())
    {
        $this->_handleOptions($options);

        $mongo_collection = $this->_getMongoCollection($collection);
    
        $filter = [ '$text' => [ '$search' => $text ]];
        $query = new Query($filter);
    
        $conn = $this->getConnection();
        $cursor = $conn->executeQuery($mongo_collection, $query);

        $retarr = array();
        foreach ($cursor as $data) {
            $this->_convertStdClass($data);
            $retarr[] = $data;
        }
        return $retarr;
    }

    /* 
     * For available options to convert the basic ones:
     * http://mongodb.github.io/mongo-php-library/api/source-class-MongoDB.Operation.Find.html 
     */
    private function _handleOptions(&$options)
    {
        $options = array_change_key_case($options, CASE_LOWER);
        if (isset($options['orderby'])) {
            $options['sort'] = array();
            foreach ($options['orderby'] as $orderBy) {
                $order = $orderBy[1] == "ASC" ? 1 : -1;
                $options['sort'][$orderBy[0]] = $order;
            }
            unset($options['orderby']);
        }
    }

    private function _getMongoCollection($collection)
    {
        if (!$collection) {
            throw new \InvalidArgumentException("Got no collection to manage.");
        }
        // Lazy, just adding a default. (And since it's not the db name itself,
        // and the SimpleMongo adapter using the old mongo driver does not use
        // this, it's a BC thingie aswell.
        /*
         *  But, 1.0.0 Release changed this one. the __construct wants the DB name and collection separately.
        if (!preg_match("/\s+\./", $collection)) {
            $collection = $this->dbname . "." . $collection;
        }
        */

        // return $this->mongodb_manager->selectCollection($collection);
        return new Collection($this->mongodb_manager, $this->dbname, $collection);
    }

    /* Does a bit more than this, since it replaces the ID key _id with the id
     * key. */
    /* This should later be reaplced with a "Set default TypeMap" option in the
     * Library. Re: https://jira.mongodb.org/browse/PHPLIB-138 Until then I do
     * this insetead of handling the cursor all over the place.
     */
    private function _convertStdClass(&$data, $new_id = null)
    {
        $data_arr = json_decode(json_encode($data), true);
        // TODO: This should be a part of the meta data, not on the object
        // itself. (And always available, somehow.)
        $id_key = 'id';
        if (isset($data_arr['_metadata']) 
                && isset($data_arr['_metadata']['_id_key'])) {
            $id_key = $data_arr['_metadata']['_id_key'];
        } elseif (isset($data_arr['_id_key'])) {
            $id_key = $data_arr['_id_key'];
        }

        if ($new_id) {
            $data_arr[$id_key] = $new_id;
        } else {
            $data_arr[$id_key] = (string)$data->_id;
        }

        unset($data_arr['_id']);
        $data = $data_arr;
        return $data;
    }
}
