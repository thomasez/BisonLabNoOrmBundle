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

  /*
   * Options, why not?
   * For now I would like these:
   *  - orderBy
   *  - limit
   *
   * For the adaptors, implement what you are able to.
   */
  public function findAll($collection, $options = array());

  public function findOneById($collection, $id_key, $id, $options = array());

  public function findOneByKeyVal($collection, $key, $val, $options = array());

  public function findByKeyVal($collection, $key, $val, $options = array());

}
