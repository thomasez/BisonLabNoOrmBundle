<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

interface ServiceInterface
{

  public function save($data, $collection = null);

  public function remove($data, $collection = null);

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
  
  // public function call($resource, $method = 'GET', $data = array());

}
