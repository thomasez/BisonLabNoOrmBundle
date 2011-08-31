<?php

namespace RedpillLinpro\NosqlBundle\Annotations;

class Relates extends \Doctrine\Common\Annotations\Annotation
{
    public $model;
    public $collection = false;
    public $resource;
}