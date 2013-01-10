<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2012 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Services;

class DBLibReadonly implements ServiceInterfaceReadonly
{

    private $connection;

    public function __construct($dbhost, $dbport = 1433, $dbname, $dbuser, $dbpasswd)
    {
        $dsn = 'dblib:host='.$dbhost.':'.$dbport.';dbname='.$dbname;
        $this->connection = new \PDO($dsn, $dbuser, $dbpasswd);
    }

    public function findOneById($table, $id, $params = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table.' WHERE id  = :id');

        $q->execute(array(
            ':id' => $id
            ));

        $data = $q->fetch(\PDO::FETCH_ASSOC);
        return $data;

    }
    
    public function findOneByKeyVal($table, $key, $val, $params = array())
    {

        if (is_string($val)) {
            $value = mb_convert_encoding($val, "ISO-8859-1");
        } else {
            $value = $val;
        }

        if ($key == "End") { 
            $key = '[End]'; 
        }

        $q = $this->connection->prepare('SELECT * from '.$table 
                .' WHERE '.$key.'=:val');

        $q->execute(array(
            ':val' => $value
            ));
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }
    
    public function findByKeyVal($table, $key, $val, $params = array())
    {
        if (is_string($val)) {
            $value = mb_convert_encoding($val, "ISO-8859-1");
        } else {
            $value = $val;
        }

        if ($key == "End") { 
            $key = '[End]'; 
        }

        $q = $this->connection->prepare('SELECT * from '.$table 
                .' WHERE '.$key.' = :val');

        $q->execute(array(
            ':val' => $value
            ));
        $data = $q->fetchall();
        return $data;
    }

    public function findAll($table, $params = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table);
        $q->execute();
        $data = $q->fetchall();
        return $data;
    }
    

}
