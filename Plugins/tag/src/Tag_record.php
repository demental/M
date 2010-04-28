<?php
/**
 * Table Definition for tag_record
 */


class DataObjects_Tag_record extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tag_record';                      // table name
    public $id;                              // bigint(8)  primary_key not_null unsigned
    public $tag_id;                          // int(4)   not_null unsigned
    public $record_id;                       // varchar(36)   not_null
    public $tagged_table;                    // varchar(50)   not_null
    public $tagged_at;                      // datetime()   not_null default_0000-00-00%2000%3A00%3A00
    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Tag_record',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'tag_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'record_id' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'tagged_table' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'tagged_at' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE + DB_DATAOBJECT_TIME + DB_DATAOBJECT_NOTNULL,             
         );
    }

    function keys()
    {
         return array('id');
    }

    function sequenceKey() // keyname, use native, native name
    {
         return array('id', true, false);
    }

    function defaults() // column default values 
    {
         return array(
             '' => null,
         );
    }

    function reverseLinks() {
        // reverseLinks generated from .links.ini file
        return array(

        );
    }
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    public function links()
    {
      return array(
        'tag_id'=>'tag:id',
        'record_id'=>$this->tagged_table.':id');
    }

    public function insert()
    {
      $this->tagged_at = date('Y-m-d H:i:s');      
      if($ret = parent::insert()) {
        $this->addHistory('add');
        $this->getTag()->incrementCount();
        
        return $ret;
      }
      return false;
    }
    public function delete()
    {
      if($ret = parent::delete()) {
        $this->addHistory('del');
        $this->getTag()->decrementCount();
        return $ret;
      }
      return false; 
    }
    public function setTag($tag)
    {
      $this->tag_id = $tag->id;
    }
    public function setRecord(DB_DataObject $record)
    {
      $this->record_id = $record->pk();
      $this->tagged_table = $record->tableName();
    }
    public function getTag()
    {
      $t = DB_DataObject::factory('tag');
      $t->id = $this->tag_id;
      $t->find(true);
      return $t;
    }
    public function addHistory($direction)
    {
      if(!in_array($direction,array('add','del'))) {
        trigger_error(__('unknown tag direction %s',array($direction)));
        return false;
      }
      $th = DB_DataObject::factory('tag_history');
      $th->tag_id = $this->tag_id;
      $th->record_id = $this->record_id;
      $th->tagged_table = $this->tagged_table;
      $th->direction = $direction;
      $th->insert();
    }
}
