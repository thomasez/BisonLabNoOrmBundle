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

  public function findAll($collection);

  public function findOneById($collection, $id);

  public function findOneByKeyVal($collection, $key, $val);

  public function findByKeyVal($collection, $key, $val);


}
