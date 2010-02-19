<?php
/**
 * Table Definition for tag_history
 */


class DataObjects_Tag_history extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tag_history';                     // table name
    public $id;                              // bigint(8)  primary_key not_null unsigned
    public $tag_id;                          // int(4)   not_null unsigned
    public $record_id;                       // varchar(36)   not_null
    public $tagged_table;                    // varchar(50)   not_null
    public $date;                            // datetime()   not_null default_0000-00-00%2000%3A00%3A00
    public $direction;                       // char(3)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Tag_history',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'tag_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'record_id' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'tagged_table' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'date' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_DATE + DB_DATAOBJECT_TIME + DB_DATAOBJECT_NOTNULL,
             'direction' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
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

        
    function links() {
        // links generated from .links.ini file
        return array(

        );
    }
    function reverseLinks() {
        // reverseLinks generated from .links.ini file
        return array(

        );
    }
    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    public function delete()
    {
      trigger_error(__('Tagging history cannot be deleted'));
      return false;
    }
    public function insert()
    {
      $this->date = date('Y-m-d H:i:s');
      return parent::insert();
    }
}