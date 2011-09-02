<?php

namespace RedpillLinpro\NosqlBundle\Annotations;

class Relates extends \Doctrine\Common\Annotations\Annotation
{
    public $manager;
    public $collection = false;
    public $resource;
}