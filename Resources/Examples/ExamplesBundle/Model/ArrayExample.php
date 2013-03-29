<?php

namespace RedpillLinpro\ExamplesBundle\Model;

use RedpillLinpro\NosqlBundle\Model\BaseModelConfigured;

class ArrayExample extends BaseModelConfigured
{

  protected static $model_setup = array(
    'Name'          => 'text', 
    'Number'        => 'integer', 
    'Comment'       => 'text', 
    'Foo'           => 'text',
    );

  protected static $classname = "ArrayExample";

  // protected static $id_key = "_id";
  protected static $id_key = "id";

}
