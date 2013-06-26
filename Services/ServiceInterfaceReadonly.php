<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2012 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Services;

interface ServiceInterfaceReadonly
{

  public function findAll($collection, $params = array());

  public function findOneById($collection, $id_key, $id, $params = array());

  public function findOneByKeyVal($collection, $key, $val, $params = array());

  public function findByKeyVal($collection, $key, $val, $params = array());
  

}
