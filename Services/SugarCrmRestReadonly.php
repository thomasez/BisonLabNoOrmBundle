<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2014 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 * This is for communicating with Sugar CRM REST API from v10, aka Sugar 7.X
 * It does depend on spinegar/sugarcrm7-api-wrapper-class
 * (But you have to read this or fine out by yourself, I cannot add it as a
 * dependency in composer.json, this it just one of many adapters.)
 *
 */

namespace RedpillLinpro\NosqlBundle\Services;

// I Only need readonly for now. Lazy? yup.
class SugarCrmRestReadonly implements ServiceInterfaceReadonly
{

    private $sugar;

    public function __construct($base_url, $username, $password)
    {
        $this->sugar = new \Spinegar\Sugar7Wrapper\Rest();
        $this->sugar->setClientOption('verify', false)
            ->setUrl($base_url . '/rest/v10/')
            ->setUsername($username)
            ->setPassword($password)
            ->connect();
    }

    public function findOneById($table, $id_key, $id, $params = array())
    {
        $data = $this->sugar->retrieve($table, $id);
        return $data;

    }
    
    public function findOneByKeyVal($table, $key, $val, $params = array())
    {
        $sopts = array_merge(array($val => $val), $params);
        $data = $this->sugar->Search($table, $sopts);

        return current($data['records']);
    }
    
    public function findByKeyVal($table, $key, $val, $params = array())
    {
        $sopts = array_merge(array($key => $val), $params);
        $data = $this->sugar->Search($table, $sopts);

        return $data['records'];
    }

    public function findAll($table, $params = array())
    {
        $data = $this->sugar->Search($table);
        return $data;
    }

}
