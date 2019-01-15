<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2016 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

class PlainPDO implements ServiceInterfaceReadonly
{
    private $connection;

    public function __construct($dbdriver, $dbhost, $dbport = 1433, $dbname, $dbuser, $dbpasswd, $dbcharset = null) 
    {
        $driver = preg_replace("/pdo_/", "", $dbdriver);
        $dsn = $driver . ':host='.$dbhost.';port='.$dbport.';dbname='.$dbname;

        if ($dbcharset)
            $dsn .= ";charset=".$dbcharset;

        $this->connection = new \PDO($dsn, $dbuser, $dbpasswd);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function findOneById($table, $id_key, $id, $options = array())
    {
        $q = $this->connection->prepare('SELECT * from '
            . $table . ' WHERE ' . $id_key . '=:id');

        if (!$q->execute(array(
                ':id' => $id
            ))) {
            throw new \Exception($q->errorInfo()[2]);
        }
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }
    
    public function findOneByKeyVal($table, $key, $val, $options = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table 
                .' WHERE ' . $key . '=:val');

        if (!$x = $q->execute(array(
            ':val' => $val
            ))) {
            throw new \Exception($q->errorInfo()[2]);
        }

        $data = $q->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }
    
    public function findByKeyVal($table, $key, $val, $options = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table 
                .' WHERE ' . $key . '=:val');
        if (!$x = $q->execute(array(
            ':val' => $val
            ))) {
            throw new \Exception($q->errorInfo()[2]);
        }
        $data = $q->fetchall(\PDO::FETCH_ASSOC);
        return $data;
    }

    public function findAll($table, $options = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table);
        $q->execute();
        // $data = $q->fetchall();
        $data = $q->fetchall(\PDO::FETCH_ASSOC);
        return $data;
    }

    /* 
     * Since this is SQL, it's gotta have a schema.
     * But we can be lazy and expect the object itself to have the right keys.
     * And we're going to.
     */
    public function save($object, $table = null)
    {
        $primary_key = null;
        if (!is_object($object)) {
            throw new \InvalidArgumentException("I need a Model/Object to be able to save.");
        }
        $primary_key = $object->getIdKey();
        $data = $object->toDataArray();

        // INSERT or UPDATE?
        if (isset($data[$primary_key])) {
            // UPDATE
            $sql = 'UPDATE '.$table .' SET ';
            $values = array();
            $keysets = array();
            foreach ($data as $key => $val) {
                $values[":".$key] = $val;
                if ($key == $primary_key) continue;
                $keysets[] = $key.'=:' . $key;
            }
            $sql .= implode(", ", $keysets);
            $sql .= " WHERE id=:id;";
            $q = $this->connection->prepare($sql);
            if (!$q->execute($values)) {
                throw new \Exception($q->errorInfo()[2]);
            }
        } else {
            // INSERT
            unset($data[$primary_key]);
            $sql = 'INSERT INTO ' . $table . ' ('
                . implode(", ", array_keys($data))
                . ' ) VALUES (:'
                . implode(", :", array_keys($data))
                . ');';
            $values = array();
            foreach ($data as $key => $val) {
                $values[":".$key] = $val;
            }
            $q = $this->connection->prepare($sql);
            if (!$q->execute($values)) {
                throw new \Exception($q->errorInfo()[2]);
            }

            // I'll just take for granted there is an autoincrement.
            // And since PostgreSQL handle that differently, while MySQL ans
            // SQLite just does it, we'll get the ID this way.
            $id = $this->connection->lastInsertId($table . '_id_seq');
            $object[$primary_key] = $id;
            return $object;
        }
    }

    public function remove($data, $table = null)
    {
        $primary_key = null;
        if (!is_object($data)) {
            throw new \InvalidArgumentException("I need a Model/Object to be able to save.");
        }
        $primary_key = $data->getIdKey();
    }
}
