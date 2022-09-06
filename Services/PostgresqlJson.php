<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011-2022 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */
namespace BisonLab\NoOrmBundle\Services;

class PostgresqlJson implements ServiceInterface
{
    private $connection;

    /* 
     * Use the database-url like Doctrine does.
     */
    public function __construct($database_url)
    {
        if (!$this->connection = pg_connect($database_url))
            throw new \Exception("Could not connect to Postgesql database");
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function save($data, $collection = null)
    {
        $table = strtolower($collection);
        if (is_object($data)) {
            $data = $data->toCompleteArray();
        }

        $pg_arr = ['data' => json_encode($data, true)];

        $id_key = 'id';
        if (isset($data['_metadata']) 
                && isset($data['_metadata']['_id_key'])) {
            $id_key = $data['_metadata']['_id_key'];
        } elseif (isset($data['_id_key'])) {
            $id_key = $data['_id_key'];
        }

        // Deciding INSERT / UPDATE
        if ($id = $data[$id_key] ?? null) {
            $where = ['id' => $id];
            if (pg_update($this->connection, $table, $pg_arr, $where))
                return $data;
            else
                throw new \Exception("Woops");
        } else {
            $result = pg_query_params($this->connection, 'INSERT INTO ' . $table . ' (data) VALUES ($1) RETURNING *', array(json_encode($data, true)));
            $blob = pg_fetch_assoc($result);
        }
        return $this->_convertBlob($blob);
    }

    public function remove($data, $collection = null)
    {
        $table = strtolower($collection);

        if (is_object($data)) {
            $id = $data->getId();
        } else {
            $id = $data;
        }
        $where = ['id' => $id];
        return pg_delete($this->connection, $table, $where);
    }

    public function findAll($collection, $options = array())
    {
        $table = strtolower($collection);
        $result = pg_query_params($this->connection, 'SELECT * FROM ' . $table . ';', []);
        $all = pg_fetch_all($result);
        $retarr = array();
        foreach ($all as $blob) {
            $data = $this->_convertBlob($blob);
            $retarr[] = $data;
        }
        return $retarr;
    }
    
    public function findOneById($collection, $id_key, $id, $options = array())
    {
        // Not sure if this is the right way or if I should throw an 
        // exception. But since I dislike exceptions. (Yes, I am using them..)
        if (empty($id)) { return null; }
        $table = strtolower($collection);

        $result = pg_query_params($this->connection, 'SELECT * FROM ' . $table . ' WHERE ID=$1;', array($id));
        $blob = pg_fetch_assoc($result);
        return $this->_convertBlob($blob);
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
        $table = strtolower($collection);
        // For now there are none I care about.
        $this->_handleOptions($options);

        $result = pg_query_params($this->connection, 'SELECT * FROM ' . $table . ' WHERE data @> $1', array(json_encode($criterias, true)));
        $blob = pg_fetch_assoc($result);
        if ($blob)
            return $this->_convertBlob($blob);
        return null;
    }
    
    public function findByKeyValAndSet($collection, $criterias, $options = array())
    {
        $table = strtolower($collection);
        // For now there are none I care about.
        $this->_handleOptions($options);

        $result = pg_query_params($this->connection, 'SELECT * FROM ' . $table . ' WHERE data @> $1', array(json_encode($criterias, true)));
        $all = pg_fetch_all($result);
        if (!is_array($all))
            return [];
        $retarr = array();
        foreach ($all as $blob) {
            $data = $this->_convertBlob($blob);
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
        $table = strtolower($collection);
    
        $filter = [ '$text' => [ '$search' => $text ]];
        // *very* simple.
        $result = pg_query_params($this->connection, "SELECT * FROM " . $table . " WHERE (data #>> '{}') ~  $1", array($text));
        $all = pg_fetch_all($result);
        if (!is_array($all))
            return [];
        $retarr = array();
        foreach ($all as $blob) {
            $data = $this->_convertBlob($blob);
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
        if (isset($options['orderBy'])) {
            $options['sort'] = array();
            foreach ($options['orderBy'] as $orderBy) {
                $order = $orderBy[1] == "ASC" ? 1 : -1;
                $options['sort'][$orderBy[0]] = $order;
            }
            unset($options['orderBy']);
        }
    }

    /* Does a bit more than this, since it replaces the ID key _id with the id
     * key. */
    /* 
     * This should later be repalced with a "Set default TypeMap" option in the
     * Library. Re: https://jira.mongodb.org/browse/PHPLIB-138 Until then I do
     * this insetead of handling the cursor all over the place.
     *
     * Not sure I need this "id_key" thingie at all.
     */
    private function _convertBlob(&$blob)
    {
        // The blob 
        $data = json_decode($blob['data'], true);
        // TODO: This should be a part of the meta data, not on the object
        // itself. (And always available, somehow.)
        $id_key = 'id';
        if (isset($data['_metadata']) 
                && isset($data['_metadata']['_id_key'])) {
            $id_key = $data['_metadata']['_id_key'];
        } elseif (isset($data['_id_key'])) {
            $id_key = $data['_id_key'];
        }

        $data[$id_key] = $blob['id'];
        return $data;
    }
}
