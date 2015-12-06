<?php

namespace BisonLab\ExamplesBundle\Manager;

use BisonLab\NoOrmBundle\Manager\BaseManager;

class ArrayExample extends BaseManager
{

  // The MongoDB Collection name, should be the same as the base model name.
  protected static $_collection = 'ArrayExample';
  protected static $_model       = '\BisonLab\ExamplesBundle\Model\ArrayExample';

    public function __construct($access_service, $options = array())
    {
        $options['model'] = self::$_model;
        $options['entity_resource'] = $options['new_entity_resource'] = strtolower(self::$_collection);
        $options['collection_resource'] = self::$_collection;

        parent::__construct($access_service, $options);
    }


}
