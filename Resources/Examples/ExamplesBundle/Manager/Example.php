<?php

namespace RedpillLinpro\ExamplesBundle\Manager;

use RedpillLinpro\NosqlBundle\Manager\BaseManager;

class Example extends BaseManager
{

  // The MongoDB Collection name, should be the same as the base model name.
  protected static $_collection = 'Example';
  protected static $_model       = '\RedpillLinpro\ExamplesBundle\Model\Example';

    public function __construct($access_service, $options = array())
    {
        $options['model'] = self::$_model;
        $options['collection_resource'] = self::$_collection;

        parent::__construct($access_service, $options);
    }


}
