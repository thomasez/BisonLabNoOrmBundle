<?php

namespace RedpillLinpro\ExamplesBundle\Manager;

use RedpillLinpro\NosqlBundle\Manager\BaseManagerMongo;

class ExampleManagerMongo extends BaseManagerMongo
{

  // The MongoDB Collection name, should be the same as the base model name.
  protected static $collection = 'Example';
  protected static $model       = '\RedpillLinpro\ExamplesBundle\Model\Example';

}
