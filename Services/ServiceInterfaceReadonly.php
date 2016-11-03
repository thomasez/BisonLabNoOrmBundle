<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2012 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

interface ServiceInterfaceReadonly
{
  // This is odd and yes, it will return something different for each
  // adapter/service. Use with care.
  public function getConnection();

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
