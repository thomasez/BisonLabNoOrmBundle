<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2012 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 * Warning: This is pretty untested, the only I've tested with is
 * findOneByKeyVal, but it should work.
 *
 */

namespace BisonLab\NoOrmBundle\Services;

class MSSqlReadonly implements ServiceInterfaceReadonly
{

    private $connection;

    public function __construct($dbhost, $dbport = 1433, $dbname, $dbuser, $dbpasswd)
    {
        // Connect to mssql server 
        $this->connection = mssql_connect($dbhost . ":" . $dbport, $dbuser, $dbpasswd);
        // Select a database 
        $db = mssql_select_db($dbname, $this->connection);
    }

    public function findOneById($table, $id_key, $id, $options = array())
    {

        if (is_int($id)) {
            $sql = 'SELECT * from '.$table .' WHERE '.$id_key."=" . $id . ";";
        } else {
            $sql = 'SELECT * from '.$table .' WHERE '.$id_key."='" . $id . "';";
        }
        // Nicked from
        // http://stackoverflow.com/questions/3252651/how-do-you-escape-quotes-in-a-sql-query-using-php
        // But not used.
        // $escaped_sql = str_replace("'", "''", $sql);
        $result = mssql_query($sql, $this->connection);

        // No iteration, we'll pick the first one.
        $data = mssql_fetch_array($result);
        mssql_free_result($result);
        return $data;

    }
    
    public function findOneByKeyVal($table, $key, $val, $options = array())
    {
        if (is_string($val)) {
            $value = utf8_decode($val);
        } else {
            $value = $val;
        }

        // This must be a hack.. Need to fix it somehow.
        if ($key == "End") { 
            $key = '[End]'; 
        }

        if (is_int($value)) {
            $sql = 'SELECT * from '.$table .' WHERE '.$key."=" . $value . ";";
        } else {
            $sql = 'SELECT * from '.$table .' WHERE '.$key."='" . $value . "';";
        }

        // Nicked from
        // http://stackoverflow.com/questions/3252651/how-do-you-escape-quotes-in-a-sql-query-using-php
        // But not used.
        // $escaped_sql = str_replace("'", "''", $sql);
        // $result = mssql_query($escaped_sql, $this->connection);
        $result = mssql_query($sql, $this->connection);

        // No iteration, we'll pick the first one.
        $data = mssql_fetch_array($result);
        mssql_free_result($result);

        return $data;
    }
    
    public function findByKeyVal($table, $key, $val, $options = array())
    {
        if (is_string($val)) {
            $value = utf8_decode($val);
        } else {
            $value = $val;
        }

        // This must be a hack.. Need to fix it somehow.
        if ($key == "End") { 
            $key = '[End]'; 
        }

        if (is_int($val)) {
            $sql = 'SELECT * from '.$table .' WHERE '.$key."=" . $value . ";";
        } else {
            $sql = 'SELECT * from '.$table .' WHERE '.$key."='" . $value . "';";
        }
        // Nicked from
        // http://stackoverflow.com/questions/3252651/how-do-you-escape-quotes-in-a-sql-query-using-php
        $escaped_sql = str_replace("'", "''", $sql);
        $result = mssql_query($sql, $this->connection);

        $data = array();
        while ($row = mssql_fetch_array($result)) {
            $data[] = $row;
        }
        mssql_free_result($result);

        return $data;
    }

    public function findAll($table, $options = array())
    {

        $sql = 'SELECT * from '.$table .';';
        $result = mssql_query($sql, $this->connection);

        // No iteration, we'll pick the first one.
        $data = array();
        while ($row = mssql_fetch_array($result)) {
            $data[] = $row;
        }
        mssql_free_result($result);

        return $data;
    }
    

}
