<?php

namespace RedpillLinpro\ExamplesBundle\Manager;

use RedpillLinpro\NosqlBundle\Manager\BaseManager;

class Example extends BaseManager
{

  // The MongoDB Collection name, should be the same as the base model name.
  protected static $collection = 'Example';
  protected static $model       = '\RedpillLinpro\ExamplesBundle\Model\Example';

}
