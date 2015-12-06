<?php

namespace BisonLab\NoOrmBundle\Annotations;

class Relates extends \Doctrine\Common\Annotations\Annotation
{
    public $manager;
    public $collection = false;
    public $resource;
}