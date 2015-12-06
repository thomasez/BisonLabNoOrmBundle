<?php

namespace BisonLab\NoOrmBundle\Annotations;

class Extract extends \Doctrine\Common\Annotations\Annotation
{
    public $columns;
    
    public function hasColumns()
    {
        return (bool) !empty($this->columns);
    }
    
}