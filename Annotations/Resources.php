<?php

namespace BisonLab\NoOrmBundle\Annotations;

class Resources extends \Doctrine\Common\Annotations\Annotation
{
    public $new_entity;
    public $entity;
    public $collection;
}