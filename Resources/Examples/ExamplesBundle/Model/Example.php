<?php

namespace RedpillLinpro\ExamplesBundle\Model;

use RedpillLinpro\NosqlBundle\Model\BaseModel;

class Example extends BaseModel
{

  protected static $model_setup = array(
    'Name'            => 'text', 
    'Number'          => 'integer', 
    'Comment'         => 'text', 
    'Foo'             => 'text',
    );

  protected static $classname = "Example";

}
