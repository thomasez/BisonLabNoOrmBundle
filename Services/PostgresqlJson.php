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
    private $dsn;

    /* 
     * Use the database-url like Doctrine does.
     */
    public function __construct($dsn)
    {
        // If we use the DATABASE_URL from Doctrine it's just too much
        // informatrion in it. Alas we'd better cut it.
        $this->dsn = preg_replace("/\?server.*/", "", $dsn);
    }

    public function setConnectionOptions(mixed $options): void
    {
        if (isset($options['dsn']))
            $this->dsn = $options['dsn'];
    }

    public function getConnection()
    {
        if (!$this->connection)
            if (!$this->connection = pg_connect($this->dsn))
                throw new \Exception("Could not connect to Postgesql database");
        return $this->connection;
    }

    public function save($data, $collection = null)
    {
        $table = strtolower($collection);
        if (is_object($data)) {
            $data = $data->toCompleteArray();
        }

        $id_key = 'id';
        if (isset($data['_metadata']) 
                && isset($data['_metadata']['_id_key'])) {
            $id_key = $data['_metadata']['_id_key'];
        } elseif (isset($data['_id_key'])) {
            $id_key = $data['_id_key'];
        }

        // Deciding INSERT / UPDATE
        if ($id = $data[$id_key] ?? null) {
            if (pg_query_params($this->getConnection(), 'UPDATE ' . $table . ' set data=$1 where id=$2;', array(json_encode($data, true), $id)))
                return $data;
            else
                throw new \Exception("Woops");
        } else {
            if ($result = pg_query_params($this->getConnection(), 'INSERT INTO ' . $table . ' (data) VALUES ($1) RETURNING *', array(json_encode($data, true))))
                $blob = pg_fetch_assoc($result);
            else
                throw new \Exception("Woops");
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
        return pg_delete($this->getConnection(), $table, $where);
    }

    public function findAll($collection, $options = array())
    {
        $retarr = array();
        $table = strtolower($collection);
        if ($result = pg_query_params($this->getConnection(), 'SELECT * FROM ' . $table . ';', [])) {
            $all = pg_fetch_all($result);
            foreach ($all as $blob) {
                $data = $this->_convertBlob($blob);
                $retarr[] = $data;
            }
        }
        return $retarr;
    }
    
    public function findOneById($collection, $id_key, $id, $options = array())
    {
        // Not sure if this is the right way or if I should throw an 
        // exception. But since I dislike exceptions. (Yes, I am using them..)
        if (empty($id)) { return null; }
        $table = strtolower($collection);

        if ($result = pg_query_params($this->getConnection(), 'SELECT * FROM ' . $table . ' WHERE ID=$1;', array($id))) {
            $blob = pg_fetch_assoc($result);
            return $this->_convertBlob($blob);
        }
        return null;
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
        $ostring = $this->_handleOptions($options, $collection);

        $query = 'SELECT * FROM ' . $table . ' WHERE data @> $1';
        if (!empty($ostring))
            $query .= " " . $ostring;
        if ($result = pg_query_params($this->getConnection(), $query, array(json_encode($criterias, true)))) {
            $blob = pg_fetch_assoc($result);
            if ($blob)
                return $this->_convertBlob($blob);
        }
        return null;
    }
    
    public function findByKeyValAndSet($collection, $criterias, $options = array())
    {
        $table = strtolower($collection);
        $retarr = array();
        $ostring = $this->_handleOptions($options, $collection);
        $query = 'SELECT * FROM ' . $table . ' WHERE data @> $1';
        if (!empty($ostring))
            $query .= " " . $ostring;
        if ($result = pg_query_params($this->getConnection(), $query, array(json_encode($criterias, true)))) {
            $all = pg_fetch_all($result);
            foreach ($all as $blob) {
                $data = $this->_convertBlob($blob);
                $retarr[] = $data;
            }
        }
        return $retarr;
    }
    
    /*
     * Very simple, until I need more advanced features.
     */ 
    public function simpleTextSearch($collection, $fields, $text, $options = array())
    {
        $ostring = $this->_handleOptions($options, $collection);
        $table = strtolower($collection);
        $retarr = array();
    
        $filter = [ '$text' => [ '$search' => $text ]];
        // *very* simple.
        if ($result = pg_query_params($this->getConnection(), "SELECT * FROM " . $table . " WHERE (data #>> '{}') ~  $1", array($text))) {
            $all = pg_fetch_all($result);
            if (!is_array($all))
                return [];
            foreach ($all as $blob) {
                $data = $this->_convertBlob($blob);
                $retarr[] = $data;
            }
        }
        return $retarr;
    }

    /* 
     * For available options to convert the basic ones:
     * http://mongodb.github.io/mongo-php-library/api/source-class-MongoDB.Operation.Find.html 
     */
    private function _handleOptions($options, $collection)
    {
        $options = array_change_key_case($options, CASE_LOWER);
        $ostring = '';
        if (isset($options['orderby'])) {
            $ostring = "ORDER BY";
            $osa = [];
            foreach ($options['orderby'] as $orderBy) {
                $osa[] = " data->>'" . $orderBy[0] . "' " . $orderBy[1];
            }
            $ostring .= join(",", $osa);
        }
        return $ostring;
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
