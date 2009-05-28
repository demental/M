<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Manages tags for any database record.
 * Logs tag add/remove history
 * This plugin needs 3 tables (@see install.php)
 */

class DB_DataObject_Plugin_Tag extends M_Plugin {

  public function getEvents()
  {
    return array('addtag','removetag','removetags','getbytags','postdelete');
  }
  
  /**
   * adds a tag to a record
   * @param DataObject_Tag tag to add
   * @param DB_DataObject database record to tag
   */

  public function addTag(DataObject_Tag $tag, DB_DataObject $obj)
  {
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->tag_id = $tag->id;
    $dbo->record_id = $obj->pk();
    $dbo->tagged_table = $obj->tableName();
    if(!$dbo->find(true)) {
      $dbo->insert();
    }
    return;
  }

  /**
   * removes a tag from a record
   * @param DataObject_Tag tag to remove
   * @param DB_DataObject database record to untag
   */  


  public function removeTag(DataObject_Tag $tag, DB_DataObject $obj)
  {
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    $dbo->setRecord($obj);
    if($dbo->find(true)) {
      $dbo->delete();
    }
    return;
  }


  /**
   * removes all tags from a record
   * @param DB_DataObject database record to untag
   */  

  public function removeTags(DB_DataObject $obj)
  {
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setRecord($obj);
    $dbo->find();
    while($dbo->fetch()) {
      $dbo->delete();
    }
    return;
  }


  /**
   * Prepares a select query, given a series of tags.
   * @param $tags mixed. Can be a tag table recordset or an array of tag records
   * @param DB_DataObject $obj the recordset on which the select query will be executed 
   */

  public function getByTags($tags, DB_DataObject $obj)
  {
    foreach($tags as $atag) {
      $tagsid[] = $atag->pk();
    }
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->whereAdd('tag_record.tag_id in('.explode(',',$tagsid).')');
    $dbo->whereAdd('tag_record.tagged_table="'.$obj->tableName().'"');
    $dbo->selectAdd();
    $obj->joinAdd($dbo);
    return;
  }
  public function getTagged(DB_DataObject $tag)
  {
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    $dbo->find();
    return $dbo;
  }
  public static function countTagged(DB_DataObject $tag)
  {
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    return $dbo->count();
  }
  public function postdelete($obj)
  {
    $this->removeTags($obj);
  }
}