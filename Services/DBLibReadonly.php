<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2012 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

class DBLibReadonly implements ServiceInterfaceReadonly
{
    private $connection;

    public function __construct($dbhost, $dbport = 1433, $dbname, $dbuser, $dbpasswd)
    {
        $dsn = 'dblib:host='.$dbhost.':'.$dbport.';dbname='.$dbname;
        $this->connection = new \PDO($dsn, $dbuser, $dbpasswd);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function findOneById($table, $id_key, $id, $options = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table.' WHERE :id_key  = :id');

        $q->execute(array(
            ':id_key' => $id_key,
            ':id' => $id
            ));
        $data = $q->fetch(\PDO::FETCH_ASSOC);
        return $data;
    }
    
    public function findOneByKeyVal($table, $key, $val, $options = array())
    {
        if (is_string($val)) {
            $value = mb_convert_encoding($val, "ISO-8859-1");
        } else {
            $value = $val;
        }

        if ($key == "End") { 
            $key = '[End]'; 
        }

        // I still wonder how I ended up making it like this. But I'm pretty
        // sure I had a good reason.
/*
        $q = $this->connection->prepare('SELECT * from '.$table 
                .' WHERE '.$key.'=:val');
        $x = $q->execute(array(
            ':val' => $value
            ));
*/
        $sql = 'SELECT * from '.$table .' WHERE '.$key."='" . $val . "';";
        $q = $this->connection->prepare($sql);
        $x = $q->execute();

        $data = $q->fetch(\PDO::FETCH_ASSOC);

        return $data;
    }
    
    public function findByKeyVal($table, $key, $val, $options = array())
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

    public function findAll($table, $options = array())
    {
        $q = $this->connection->prepare('SELECT * from '.$table);
        $q->execute();
        $data = $q->fetchall();
        return $data;
    }
}
