<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace RedpillLinpro\NosqlBundle\Model;

interface StorableObjectInterface
{

    public function fromDataArray($data, \RedpillLinpro\NosqlBundle\Manager\BaseManager $manager);

    public function toDataArray();

    public static function getClassName();

    public function setDataArrayIdentifierValue($identifier_value);

    public function getDataArrayIdentifierValue();

    public function hasDataArrayIdentifierValue();

}
