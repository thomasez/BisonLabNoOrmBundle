<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Model;

interface StorableObjectInterface
{
    public function fromDataArray($data, \BisonLab\NoOrmBundle\Manager\BaseManager $manager);

    public function toDataArray();

    public static function getClassName();

    public function setDataArrayIdentifierValue($identifier_value);

    public function getDataArrayIdentifierValue();

    public function hasDataArrayIdentifierValue();
}
