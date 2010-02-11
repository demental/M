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
 * This plugin needs 3 tables (@see Plugins/tag/commandes/install.php)
 */

class DB_DataObject_Plugin_Tag extends M_Plugin {

  public function getEvents()
  {
    return array('addtagstoform','searchbytags','addtag','removetag','removetags','getbytags','postdelete','hastag','gettaglasthistory','gettags');
  }
  
  /**
   * Adds tag checkboxes to form passed as first parameter
   * 2nd parameter is the actual name of the fields used to add tags
   * @param HTML_QuickForm 
   * @param string default _tags
   */
  public function addTagsToForm(HTML_QuickForm $form, $fieldname, DB_DataObject $obj)
  {
    Log::info('Adding tags to form');
    $tags = DB_DataObject::factory('tag');
    $tags->archived=0;// @todo add this field to tags table
    $tag_record = DB_DataObject::factory('tag_record');
    $tags->joinAdd($tag_record);
    $tags->whereAdd("tag_record.tagged_table = '".$obj->__table."'");
    $tags->selectAdd();
    $tags->selectAdd('tag.*');
    $tags->groupBy('tag.strip');
    if ($tags->find()) {
      while($tags->fetch()) {
        $taglist[$tags->id] = $tags->strip;
      }
      foreach($taglist as $id=>$strip) {
        $arr[] = HTML_QuickForm::createElement('checkbox',$fieldname.'['.$id.']','',$strip);
        $arr2[] = HTML_QuickForm::createElement('checkbox','exc_'.$fieldname.'['.$id.']','',$strip);      
      }

      $grp = HTML_QuickForm::createElement('group',$fieldname,__('Including Tags'),$arr,null,false);
      $grp2 = HTML_QuickForm::createElement('group','exc_'.$fieldname,__('Excluding Tags'),$arr2,null,false);
  
      if($form->elementExists('__submit__')) {
        $form->insertElementBefore($grp,'__submit__');
        $form->insertElementBefore($grp2,'__submit__');
      } else {
        $form->addElement($grp);
        $form->addElement($grp2);      
      }
    }
  }
  
  /**
   * Prepares query to retreive filtering by tags
   * @param array(tagID1,tagID2,....tagIDn) tags to include
   * @param array(tagID1,tagID2,....tagIDn) tags to exclude
   * 
   */
   public function searchByTags($tags,$excludeTags,DB_DataObject $obj)
   {
     if((!is_array($tags) || count($tags)==0) && (!is_array($excludeTags) || count($excludeTags)==0)) return;
     $obj->selectAs();
     foreach($tags as $tag) {
       $t = DB_DataObject::factory('tag_record');
       $t->tag_id = $tag;
       $t->tagged_table = $obj->tableName();
       $t->selectAdd();
       $t->selectAdd('tag_record.tag_id');
       $obj->joinAdd(clone($t),array('joinType'=>'INNER','joinAs'=>'tags_'.$tag,'useWhereAsOn'=>true));
      }
      foreach($excludeTags as $tag) {
        $t = DB_DataObject::factory('tag_record');
        $t->whereAdd('extags_'.$tag.'.tag_id = '.$tag);
        $t->tagged_table = $obj->tableName();
        $obj->whereAdd('extags_'.$tag.'.id is null');
        $t->selectAdd();
        $t->selectAdd('extags_'.$tag.'.tag_id');
        $obj->joinAdd(clone($t),array('joinType'=>'LEFT','joinAs'=>'extags_'.$tag,'useWhereAsOn'=>true));
      }
   }
  /**
   * adds a tag to a record
   * @param mixed : DataObject_Tag tag to add or string.
   * @param DB_DataObject database record to tag
   */

  public function addTag($tag, DB_DataObject $obj)
  {
    if(!$existingtag = $this->_getTagFromTag($tag)) {
      $newtag = DB_DataObject::factory('tag');
      $newtag->strip = $tag;
      $newtag->insert();
      $tag = $newtag;
    } else {
      $tag = $existingtag;
    }
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->tag_id = $tag->id;
    $dbo->record_id = $obj->pk();
    $dbo->tagged_table = $obj->tableName();
    if(!$dbo->find(true)) {
      $dbo->insert();
    }

    $this->triggerTag($tag,'add',$obj);
    return $this->returnStatus($obj);
  }

  public function triggerTag($tag,$trigger,$obj)
  {
    $strip = Strings::stripify($tag->strip,true);
    $classes = array(
      APP_ROOT.PROJECT_NAME.'/tags/'.strtolower($strip).'/'.strtolower($obj->tableName()).'.php'=>strtolower('subtagtrigger_'.$strip.'_'.$obj->tableName()),
      APP_ROOT.PROJECT_NAME.'/tags/'.strtolower($strip).'.php'=>strtolower('tagtrigger_'.$strip)
      );
    foreach($classes as $file=>$class) {
      if(class_exists($class)) {    
        call_user_func_array(array($class,'on'.$trigger),array($obj));
        break;
      }
      if(file_exists($file)) {
        require_once $file;
        call_user_func_array(array($class,'on'.$trigger),array($obj));
        break;
      }
    }
  }
  /**
   * removes a tag from a record
   * @param DataObject_Tag tag to remove
   * @param DB_DataObject database record to untag
   */  


  public function removeTag($tag, DB_DataObject $obj)
  {
    if(!$tag = $this->_getTagFromTag($tag)) {return $this->returnStatus($obj);} 
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    $dbo->setRecord($obj);
    if($dbo->find(true)) {
      $dbo->delete();
    }
    $this->triggerTag($tag,'remove',$obj);
    return $this->returnStatus($obj);
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
    return $this->returnStatus($obj);
  }

  /**
   * returns the recordset of tags attached to $obj
   */
  public function getTags($obj)
  {
    $tag = DB_DataObject::factory('tag');
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->tagged_table = $obj->tableName();
    $dbo->record_id = $obj->pk();
    $tag->selectAdd();
    $tag->selectAdd('tag.id,tag.strip');
    $tag->joinAdd($dbo);
    $tag->selectAs($dbo,'link_%s');
    $tag->find();
    return $this->returnStatus($tag);
  }
  
  /**
   * Prepares a select query, given a series of tags.
   * @param $tags mixed. Can be a tag table recordset or an array of tag records
   * @param DB_DataObject $obj the recordset on which the select query will be executed 
   */

  public function getByTags($tags, DB_DataObject $obj)
  {
    if(!is_array($tags) || $tags->N) {
      $tags = array($tags);
    }
    foreach($tags as $atag) {
      if(!$atag = $this->_getTagFromTag($atag)) {continue;}
      $tagsdo[] = $atag;
    }
    $obj->selectAs();
    foreach($tagsdo as $tag) {
      $t = DB_DataObject::factory('tag_record');
      $t->tag_id = $tag->pk();
      $t->tagged_table = $obj->tableName();
      $obj->joinAdd($t,'inner','tags_'.$tag);

     }
    return;
  }
  public function getTagged($tag)
  {
    if(!$tag = $this->_getTagFromTag($tag)) {return $this->returnStatus(false);}
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    $dbo->find();
    return $dbo;
  }
  public function hastag($tag,$obj)
  {
    if(!$tag = $this->_getTagFromTag($tag)) {return $this->returnStatus(false);}
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->tag_id = $tag->id;
    $dbo->setRecord($obj);
    $res = $dbo->count();
    if($res<1) {
        return $this->returnStatus(false);
    }
    return $this->returnStatus(true);
  }
  public function getTagLastHistory($tag,$direction,DB_DataObject $obj)
  {
    if(!$tag = $this->_getTagFromTag($tag)) {return $this->returnStatus(false);}
    $h = DB_DataObject::factory('tag_history');
    $h->tag_id = $tag->id;
    $h->record_id = $obj->pk();
    $h->tagged_table = $obj->tableName();
    $h->direction=$direction;
    $h->orderBy('date DESC');
    $h->find(true);
    return $this->returnStatus($h);
  }
  public static function countTagged($tag)
  {
        if(!$tag = $this->_getTagFromTag($tag)) {return $this->returnStatus(0);}
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    return $this->returnStatus($dbo->count());
  }
  public function postdelete($obj)
  {
    $this->removeTags($obj);
  }
  protected function _getTagFromTag($tag) {
    if(!is_a($tag,'DataObjects_Tag')) {
      $t = DB_DataObject::factory('tag');
      $t->strip = $tag;
      if(!$t->find(true)) {
        return false;
      }
      $tag = $t;
    }
    return $tag;
  }
}