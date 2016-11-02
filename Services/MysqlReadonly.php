<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2013 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

class MysqlReadonly implements ServiceInterfaceReadonly
{
    private $connection;

    public function __construct($dbhost, $dbport = 1433, $dbname, $dbuser, $dbpasswd)
    {
        $dsn = 'mysql:host='.$dbhost.';port='.$dbport.';dbname='.$dbname;
        $this->connection = new \PDO($dsn, $dbuser, $dbpasswd);
    }

    public function findOneById($table, $id_key, $id, $options = array())
    {
        $sql = 'SELECT * from '.$table.' WHERE ' . $id_key . ' = :id';

        $nsql = 'SELECT * from :table WHERE :id_key  = :id';
        $q = $this->connection->prepare($sql);

        if (!$q->execute(array(
            // ':table' => $table,
            // ':id_key' => $id_key,
            ':id' => $id
            ))) {

            print_r($q->errorInfo());
            return null;

        };

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

        $q = $this->connection->prepare('SELECT * from '.$table 
                .' WHERE '.$key.'=:val');

        $q->execute(array(
            ':val' => $value
            ));
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
