<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2014 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 * This is for communicating with Sugar CRM REST API from v10 and above, aka
 * Sugar 7.X It does depend on spinegar/sugarcrm7-api-wrapper-class
 * (But you have to read this or find out by yourself, I cannot add it as a
 * dependency in composer.json, this it just one of many adapters.)
 *
 */

namespace BisonLab\NoOrmBundle\Services;

// I Only need readonly for now. Lazy? yup.
class SugarCrmRestReadonly implements ServiceInterfaceReadonly
{
    private $sugar;

    public function __construct($base_url, $username, $password, $platform = "sugar-wrapper")
    {
        if (!preg_match("/rest\/v[\d_]+/", $base_url)) {
            $base_url .= '/rest/v11_5/';
        }

        $this->sugar = new \Spinegar\SugarRestClient\Rest();
        $this->sugar
            ->setUrl($base_url)
            ->setUsername($username)
            ->setPassword($password)
            ->setPlatform($platform)
            ;
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
        } catch (\GuzzleHttp\Exception\ClientException $e) {
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
