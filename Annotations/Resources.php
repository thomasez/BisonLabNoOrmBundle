<?php

namespace RedpillLinpro\NosqlBundle\Annotations;

class Resources extends \Doctrine\Common\Annotations\Annotation
{
    public $new_unique;
    public $unique;
    public $collection;
}