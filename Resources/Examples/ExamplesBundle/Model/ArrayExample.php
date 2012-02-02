<?php

namespace RedpillLinpro\ExamplesBundle\Model;

use RedpillLinpro\NosqlBundle\Model\BaseModelArray;

class ArrayExample extends BaseModelArray
{

  protected static $model_setup = array(
    'Name'          => 'text', 
    'Number'        => 'integer', 
    'Comment'       => 'text', 
    'Foo'           => 'text',
    );

  protected static $classname = "ArrayExample";

  protected static $id_key = "_id";

}
