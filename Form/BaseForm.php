<?php

/**
 *
 * @author    Thomas Lundquist <thomasez@redpill-linpro.com>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace RedpillLinpro\NosqlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

abstract class BaseForm extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        foreach ($options['data']::getFormSetup() as $key => $type)
        {
          $builder->add($key, $type);
        }
    }

}

