<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Services;

interface ServiceInterface
{

  public function save($data, $collection = null);

  public function remove($data, $collection = null);

  public function findAll($collection, $params = array());

  public function findOneById($collection, $id, $params = array());

  public function findOneByKeyVal($collection, $key, $val, $params = array());

  public function findByKeyVal($collection, $key, $val, $params = array());


}
