<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Manages tags for any database record.
 * Logs tag add/remove history
 * This plugin needs 3 tables (@see Plugins/tag/commandes/install.php)
 */

class Plugins_Tag_DB extends M_Plugin {

  protected static $triggers_enabled = true;

  public static function enable_triggers() {
    self::$triggers_enabled = true;
  }

  public static function disable_triggers() {
    self::$triggers_enabled = false;
  }

  public function getEvents()
  {
    return array('addtagstoform','searchbytags','gettaginfo','addtag','removetag','addtagbyhuman','removetagbyhuman','removetags','getbytags','getwithouttags','gettagdate','gettagrecord','delete','hastag','gettaglasthistory','gettags','postfetch','undelete',
      'frontendsearch',
    'postpreparesearchform',
    'getbatchmethods');
  }


  public function getBatchMethods($arr,$obj)
  {
    $arr['batchaddtag'] = array('title'=>'Add/remove tags','plugin'=>'tag');
    return self::returnStatus($arr);
  }
  public function postFetch($obj)
  {
    $obj->_tagplugin_cache = null;
  }
  public function prepareBatchAddTag($form)
  {
    $form->addElement('text','add','Add...');
    $form->addElement('text','remove','And/or remove...');
    $form->addElement('checkbox','trigger','Execute triggers if exist');
  }
  public function batchAddTag($obj,$data)
  {
    if(!$data['trigger']) self::disable_triggers();
    try {
      while($obj->fetch()) {
        if($data['add']) {
          $obj->addTag($data['add']);
        }
        if($data['remove']) {
          $obj->removeTag($data['remove']);
        }
      }
    } catch(Exception $e) {
      //
    }
    self::enable_triggers();
  }
  /**
   * Adds tag checkboxes to form passed as first parameter
   * 2nd parameter is the actual name of the fields used to add tags
   * @param HTML_QuickForm
   * @param string default _tags
   */
  public function addTagsToForm(HTML_QuickForm $form, $fieldname, DB_DataObject $obj)
  {
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
        $arr[]  = MyQuickForm::createElement('checkbox',$fieldname.'['.$id.']','',$strip);
        $arr2[] = MyQuickForm::createElement('checkbox','exc_'.$fieldname.'['.$id.']','',$strip);
      }

      $grp =  MyQuickForm::createElement('group',$fieldname,__('Including Tags'),$arr,null,false);
      $grp2 = MyQuickForm::createElement('group','exc_'.$fieldname,__('Excluding Tags'),$arr2,null,false);

      if($form->elementExists('__submit__')) {
        $form->insertElementBefore($grp,'__submit__');
        $form->insertElementBefore($grp2,'__submit__');
      } else {
        $form->addElement($grp);
        $form->addElement($grp2);
      }
    }
  }
  public function postPrepareSearchform($form,$fb,DB_DataObject $obj)
  {
    $obj->addTagsToForm($form,'_tags');
  }
  public function frontEndSearch($values, DB_DataObject $obj)
  {
    $values_tags = (is_array($values['_tags'])) ? array_keys($values['_tags']) : array();
    $values_exc__tags = (is_array($values['exc__tags'])) ? array_keys($values['exc__tags']) : array();
	  $obj->searchByTags($values_tags, $values_exc__tags);
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

   public function getTagInfo($tag , DB_DataObject $obj)
   {
    if(!$existingtag = $this->_getTagFromTag($tag)) return null;
     $dbo = DB_DataObject::factory('tag_record');
     $dbo->tag_id = $tag->id;
     $dbo->record_id = $obj->pk();
     $dbo->tagged_table = $obj->tableName();
     $dbo->find(true);
     return self::returnStatus($dbo);

   }

  /**
   * adds a tag to a record
   * @param mixed : DataObject_Tag tag to add or string.
   * @param bool : is tag added by human or programatically
   * @param DB_DataObject database record to tag
   */


   public function addTag($tag,DB_DataObject $obj) {
     return $this->_addTag($tag,false,$obj);
   }
   public function addTagByHuman($tag,DB_DataObject $obj) {
     return $this->_addTag($tag,true,$obj);
   }
  protected function _addTag($tag, $byhuman, DB_DataObject $obj)
  {
    if(!$obj->pk()) return self::returnStatus($obj);
    if($this->validateTriggerTag($tag,'add',$byhuman,$obj)) {
      // $tag will be replaced by its DO object if string passed as param
      if($this->__createTagRecord($tag,$obj)) {
        $this->triggerTag($tag,'add',$obj);
        $this->clearTagCache($obj);
      }
    }
		$obj->_tagadded=1;
    return self::returnStatus($obj);
  }
  protected function __createTagRecord(&$tag,$obj)
  {
    if(!$existingtag = $this->_getTagFromTag($tag)) {
      if(empty($tag)) return false;
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
      return true;
    }
    return false;
  }
  public function clearTagCache($obj)
  {
    $obj->tagplugin_cache = '';
		$obj->_tagplugin_cache = null;
    $obj->update();
  }

  /**
   * Calls and return tag validation result if it exists (the methods are validateAdd and validateRemove)
   */
  public function validateTriggerTag($tag,$trigger,$byhuman,$obj)
  {
    $strip = Strings::stripify($tag->strip,true);
    $classes = array(
      APP_ROOT.'app/tags/__tables/'.strtolower($obj->tableName()).'.php'=>strtolower('subtagtrigger__'.$obj->tableName()),
      APP_ROOT.'app/tags/'.strtolower($strip).'/'.strtolower($obj->tableName()).'.php'=>strtolower('subtagtrigger_'.$strip.'_'.$obj->tableName()),
      APP_ROOT.'app/tags/'.strtolower($strip).'.php'=>strtolower('tagtrigger_'.$strip)
      );
    foreach($classes as $file=>$class) {
      if(class_exists($class,false)) {    // avoid autoload
        if(method_exists($class,'validate'.$trigger)) {
          $res = call_user_func_array(array($class,'validate'.$trigger),array($obj,$byhuman,$tag));
        }
        break;
      }
      if(file_exists($file)) {
        require_once $file;
        if(method_exists($class,'validate'.$trigger)) {
          $res = call_user_func_array(array($class,'validate'.$trigger),array($obj,$byhuman,$tag));
        }
        break;
      }
    }
    if($res === false) {
      return false;
    }
    return true;
  }
  /**
   * calls tag trigger if it exists
   */
  public function triggerTag($tag,$trigger,$obj)
  {
    if(!self::$triggers_enabled) return;
    $strip = Strings::stripify($tag->strip,true);
    $classes = array(
      APP_ROOT.'app/tags/'.strtolower($strip).'/'.strtolower($obj->tableName()).'.php'=>strtolower('subtagtrigger_'.$strip.'_'.$obj->tableName()),
      APP_ROOT.'app/tags/'.strtolower($strip).'.php'=>strtolower('tagtrigger_'.$strip)
      );
    foreach($classes as $file=>$class) {
      if(class_exists($class,false)) {    // avoid autoload
        if(method_exists($class,'on'.$trigger)) {
          $res = call_user_func_array(array($class,'on'.$trigger),array($obj,$byhuman));
        }
        break;
      }
      if(file_exists($file)) {
        require_once $file;
        if(method_exists($class,'on'.$trigger)) {
          $res = call_user_func_array(array($class,'on'.$trigger),array($obj,$byhuman));
        }
        break;
      }
    }
  }
  /**
   * removes a tag from a record
   * @param DataObject_Tag tag to remove
   * @param DB_DataObject database record to untag
   */
  // By code
  public function removeTag($tag,DB_DataObject $obj) {
    return $this->_removeTag($tag,false,$obj);
  }
  // By human
  public function removeTagByHuman($tag,DB_DataObject $obj) {
    return $this->_removeTag($tag,true,$obj);
  }
  protected function _removeTag($tag, $byhuman, DB_DataObject $obj)
  {
    if(!$obj->pk()) return self::returnStatus($obj);
    if(!$tag = $this->_getTagFromTag($tag)) {return self::returnStatus($obj);}
    if($this->validateTriggerTag($tag,'remove',$byhuman,$obj)) {
      $dbo = DB_DataObject::factory('tag_record');
      $dbo->setTag($tag);
      $dbo->setRecord($obj);
      if($dbo->find(true)) {
        $dbo->delete();
        $this->triggerTag($tag,'remove',$obj);
        $this->clearTagCache($obj);
      }

    }
    return self::returnStatus($obj);
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
    return self::returnStatus($obj);
  }

  /**
   * returns the recordset of tags attached to $obj
   */
  public function getTags($obj)
  {

    $tag = DB_DataObject::factory('tag');
    $tag->whereAdd('strip in ("'.implode('","',$this->getTagsArray($obj)).'")');
    $tag->find();
    return self::returnStatus($tag);
  }

  /**
   * @param DB_DataObject $obj the recordset on which the select query will be executed
   */

  public function getTagsArray($obj)
  {
    if(is_array($obj->_tagplugin_cache)) return $obj->_tagplugin_cache;
    if(empty($obj->tagplugin_cache)) {

      $tag = DB_DataObject::factory('tag');
      $dbo = DB_DataObject::factory('tag_record');
      $dbo->tagged_table = $obj->tableName();
      $dbo->record_id = $obj->pk();
      $tag->selectAdd();
      $tag->selectAdd('tag.id,tag.strip');
      $tag->joinAdd($dbo);
      $tag->selectAs($dbo,'link_%s');
      $tag->find();
      $obj->tagplugin_cache = '|';
      while($tag->fetch()) {
        if(empty($tag->strip)) continue;
        $obj->tagplugin_cache.=$tag->strip.'|';
        $obj->_tagplugin_cache[]=$tag->strip;
      }
      $db = $obj->getDatabaseConnection();
      $query = sprintf('UPDATE %s SET tagplugin_cache=:cacheval where %s = :pkval',
        $db->quoteIdentifier($obj->tableName()), $db->quoteIdentifier($obj->pkName()));
      $sth = $db->prepare($query, array('text','text'));
      if(PEAR::isError($sth)) throw new Exception($sth->getMessage().' HINT: check if your table has a tagplugin_cache field');
      $sth->execute(array('cacheval'=>$obj->tagplugin_cache,'pkval'=>$obj->pk()));

    }
    $obj->_tagplugin_cache = explode('|',$obj->tagplugin_cache);

    return $obj->_tagplugin_cache;

  }

  public function getByTags($tags, DB_DataObject $obj)
  {
    if(!is_array($tags) || $tags->N) {
      $tags = array($tags);
    }
    foreach($tags as $atag) {
      if(!$atag = $this->_getTagFromTag($atag)) {continue;}
      $tagsdo[] = $atag;
    }
    if(!is_array($tagsdo)) {
      // No tag exist, so we should retrieve no record at all,
      $obj->whereAdd('1=2');
      return;
    }
    $obj->selectAs();
    foreach($tagsdo as $tag) {
      $t = DB_DataObject::factory('tag_record');
      $t->tag_id = $tag->pk();
      $t->tagged_table = $obj->tableName();
      $obj->joinAdd($t,'inner','tags_'.Strings::stripify($tag->__toString(),true));

     }
    return;
  }
  /**
  * Prepares a select query, given a series of tags to exclude.
  * @param $tags mixed. Can be a tag table recordset or an array of tag records
  * @param DB_DataObject $obj the recordset on which the select query will be executed
  */
  public function getWithoutTags($tags,DB_DataObject $obj)
  {
    $tagsdo = array();
    if(!is_array($tags) || $tags->N) {
      $tags = array($tags);
    }
    foreach($tags as $atag) {
      if(!$atag = $this->_getTagFromTag($atag)) {continue;}
      $tagsdo[] = $atag;
    }
    foreach($tagsdo as $tag) {
      $t = DB_DataObject::factory('tag_record');
      $t->whereAdd('extags_'.$tag->id.'.tag_id = '.$tag->id);
      $t->tagged_table = $obj->tableName();
      $obj->whereAdd('extags_'.$tag->id.'.id is null');
      $t->selectAdd();
      $t->selectAdd('extags_'.$tag->id.'.tag_id');
      $obj->joinAdd(clone($t),array('joinType'=>'LEFT','joinAs'=>'extags_'.$tag->id,'useWhereAsOn'=>true));
    }
    return;
  }
  public function getTagged($tag)
  {
    if(!$tag = $this->_getTagFromTag($tag)) {return self::returnStatus(false);}
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    $dbo->find();
    return $dbo;
  }
  public function hastag($tag,$obj)
  {

    if(!$obj->pk()) return self::returnStatus(false);

    if(!$tag = $this->_getTagFromTag($tag)) return self::returnStatus(false);
    if(in_array($tag->strip,$this->getTagsArray($obj))) return self::returnStatus(true);

    return self::returnStatus(false);
  }
  public function getTagDate($tag,$obj)
  {
    if(!$tag = $this->_getTagFromTag($tag)) return self::returnStatus(false);
    if(!$obj->pk()) return self::returnStatus(false);
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->tag_id = $tag->id;
    $dbo->setRecord($obj);
    if($dbo->find(true)) {
      return self::returnStatus($dbo->tagged_at);
    }
    return self::returnStatus(false);
  }
  public function getTagRecord($tag,$obj)
  {
    if(!$tag = $this->_getTagFromTag($tag)) return self::returnStatus(false);
    if(!$obj->pk()) return self::returnStatus(false);
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->tag_id = $tag->id;
    $dbo->setRecord($obj);
    if($dbo->find(true)) {
      return self::returnStatus($dbo);
    }
    return self::returnStatus(false);
  }

  public function getTagLastHistory($tag,$direction,DB_DataObject $obj)
  {
    if(!$tag = $this->_getTagFromTag($tag)) {return self::returnStatus(false);}
    $h = DB_DataObject::factory('tag_history');
    $h->tag_id = $tag->id;
    $h->record_id = $obj->pk();
    $h->tagged_table = $obj->tableName();
    $h->direction=$direction;
    $h->orderBy('date DESC');
    $h->find(true);
    return self::returnStatus($h);
  }

  public static function countTagged($tag)
  {
    if(!$tag = self::_getTagFromTag($tag)) { return self::returnStatus(0); }
    $dbo = DB_DataObject::factory('tag_record');
    $dbo->setTag($tag);
    return self::returnStatus($dbo->count());
  }


  public function delete($obj)
  {
    $this->getTagsArray($obj);
    $this->removeTags($obj);
  }

  public function undelete($obj)
  {
    if(!$obj->pk()) return;
    foreach($this->getTagsArray($obj) as $atag) {

      $this->__createTagRecord($atag,$obj);
    }

  }
  protected static function _getTagFromTag($tag) {
    if(!is_a($tag,'DataObjects_Tag')) {
      $t = DB_DataObject_Pluggable::retreiveFromRegistry('tag','strip',$tag);
      if($t) {
        $tag = $t;
      } else {
        $t = DB_DataObject::factory('tag');
        $t->strip = $tag;
        if(!$t->find(true)) {
          return false;
        }
        $tag = $t;
        DB_DataObject_Pluggable::storeToRegistry($tag);
      }
    }
    return $tag;
  }

}
