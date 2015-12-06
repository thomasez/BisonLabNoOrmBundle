<?php

/**
 *
 * @author    Thomas Lundquist <github@bisonlab.no>
 * @copyright 2011 Thomas Lundquist
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */


namespace BisonLab\NoOrmBundle\Manager;

abstract class VersionedManager extends BaseManager
{
  /* 
   * Remember to put these in the Manages extending this one.
   * Right now they are all the same but I define different names here.
   * Or rather, they have to be defined in the object extending this one.
   */
  // protected static $_versions_collection  = 'BaseVersions';

  /* This is where we add the copying to a versioned collection. */
  /* This read was quite good:
   * http://askasya.com/post/trackversions
   * I want to keep this compatible with the basic non-versioned stuff
   * alas I cannot touch the main collection.
   * I'd also like to not having to neither find, nor update the
   * versioned/history records. Not sure why, since my priority is not
   * performance, but as long as I can do without, I will anyway.
   * What will hurt by this method is retrieving data from the versions
   * collection.  
   * So, the plan? It's shorter than the reasoning above.
   * 
   * Grab the "original"
   * Are they alike? If yes, no need to go through all this, return.
   * Create new array.
   * Set "data" to the original.
   * Set "next_version_created" to microseconds since epoch.
   * Set "id" to $object->getId();
   * Save object.
   * Save version array.
   *
   * This has one negative side effect; if you want to know when the current
   * version was created you have to find the last one saved in the versions
   * collection. Aka, you need to check the previous record to find the updated
   * timestamp.
   */

  public function save($object)
  {
    $original = array();

    // Does it exist?
    if ($object->getId()) {
        $original_object = $this->findOneById($object->getId());
        if ($original_object == $object) {
            return $object;
        }
        $original['data'] = $original_object->toDataArray();
    }

    // Save can do both insert and update with MongoDB.
    $new_data = $this->access_service->save($object, static::$_collection);

    if (isset($new_data['id']))
    {
      $object->setId($new_data['id']);
    }

    $original['next_version_created'] = round(microtime(true) * 1000);
    $original['object_id'] = $object->getId();

    $this->access_service->save($original, static::$_versions_collection);
    return $object;

  }

  /*
   * The big question here is, should we delete everything or set some
   * "DELETED" flag in the versions table and keep them there? 
   * Or plainly make it configureable. 
   * The latter, through _keep_deleted 
   * Default has to be false, aka do not keep. In case this is data that's not
   * supposed to be stored forever.. (Data retention.)
   * And if you don't know everything is stored, you will wonder why the
   * history table is as big as it is..
   */
  public function delete($object)
  {

    if (is_object($object) && $id = $object->getId()) {
        // This could be discussed as being superfluous or not, since later
        // here I'll just accept the object as being an id and just delete it.. 
        // So why bother checking this then?
        if ($object->getClassName() != static::$_collection
            && get_class($object) != static::$_model) {
            throw new \InvalidArgumentException('This is not an object I can delete. It may be a missing Id or wrong class.');
        }
    } elseif (!is_object($object)) {
       $id = $object; 
    }

    if (!$id) {
        throw new \InvalidArgumentException('This is not an object I can delete. It may be a missing Id or wrong class.');
    }

    $status = $this->access_service->remove($id, static::$_collection);
    return $status;
  }

  /*
   * I wonder if this is the only one I need.
   */
  public function findFromHistory($object, $limit = null) {
    // This is a hack, but does it matter? The laternative is to have my own
    // findByKeyVal here.
    $history = array();
    $object_id = $object instanceof static::$_model ? $object->getId() : $object;
    foreach ($this->access_service->findByKeyVal(static::$_versions_collection,
        'object_id', $object_id, array(
        // Cannot use handleOptions in the base manager, aka we need to set
        // array of arrays here.
        'orderBy' => array(array('next_version_created', 'DESC')),
        'limit' => $limit)) as $data) 
        {
        // Replacing.
        // I have no idea why or how we could end up with a data free log item,
        // but it happens..
        // I pondered about not returning log items without data, but that's
        // kinda wrong aswell. Better make the users filter, then they can
        // choose what they want.
        if (isset($data['data'])) {
            $data['data'] = new static::$_model($data['data']);
            $history[] = $data;
        }
    }
    return $history;
  }

  /*
   * No, it's not.
   */
  public function findOneFromHistoryById($id) {
    // This was kinda annoying and I'm sure I just doing it wrong.
    $m = static::$_model;
    return $this->access_service->findOneById(
        static::$_versions_collection, $m::getIdKey(), $id);
  }

}
