<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

namespace BisonLab\NoOrmBundle\Services;

interface ServiceInterface extends ServiceInterfaceReadonly
{
    public function save($data, $collection = null);

    public function remove($data, $collection = null);
}
