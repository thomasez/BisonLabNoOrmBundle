<?php

namespace RedpillLinpro\ExamplesBundle\Model;

use RedpillLinpro\NosqlBundle\Model\BaseModelArray;

class Example extends BaseModelArray
{

  protected static $model_setup = array(
    'Name'          => 'text', 
    'Number'        => 'integer', 
    'Comment'       => 'text', 
    'Foo'           => 'text',
    );

  protected static $classname = "Example";

}
