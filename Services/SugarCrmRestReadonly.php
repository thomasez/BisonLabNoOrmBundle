<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2014 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 * This is for communicating with Sugar CRM REST API from v10, aka Sugar 7.X
 * It does depend on spinegar/sugarcrm7-api-wrapper-class
 * (But you have to read this or fine out by yourself, I cannot add it as a
 * dependency in composer.json, this it just one of many adapters.)
 *
 */

namespace BisonLab\NoOrmBundle\Services;

// I Only need readonly for now. Lazy? yup.
class SugarCrmRestReadonly implements ServiceInterfaceReadonly
{
    private $sugar;

    public function __construct($base_url, $username, $password)
    {
        $this->sugar = new \Spinegar\Sugar7Wrapper\Rest("Guzzle6");
        $this->sugar->setClientOption('verify', false)
            ->setUrl($base_url . '/rest/v10/')
            ->setUsername($username)
            ->setPassword($password)
            ->connect();
    }

    public function getConnection()
    {
        return $this->sugar;
    }

    public function findOneById($table, $id_key, $id, $options = array())
    {
        // If a 404, handle it, if anything else, throw it further.
        try {
            $data = $this->sugar->retrieve($table, $id);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == 404)
                return null;
            else
                throw $e;
        }
        return $data;
    }
    
    public function findOneByKeyVal($table, $key, $val, $options = array())
    {
        $sopts = array_merge(array($val => $val), $options);
        try {
            $data = $this->sugar->Search($table, $sopts);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == 404)
                return null;
            else
                throw $e;
        }

        return current($data['records']);
    }
    
    public function findByKeyVal($table, $key, $val, $options = array())
    {
        $sopts = array_merge(array($key => $val), $options);
        // Good question; does search return 404 at all? To be honest, it 
        // shouldn't.
        try {
            $data = $this->sugar->Search($table, $sopts);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            if ($e->getResponse()->getStatusCode() == 404)
                return null;
            else
                throw $e;
        }

        return $data['records'];
    }

    public function findAll($table, $options = array())
    {
        $data = $this->sugar->Search($table);
        return $data['records'];
    }
}
