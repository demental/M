<?php
/**
 * Table Definition for tag
 */
require_once 'M/DB/DataObject/Pluggable.php';

class DataObjects_Tag extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'tag';                             // table name
    public $id;                              // int(4)  primary_key not_null
    public $strip;                           // varchar(30)   not_null
    public $description;                     // mediumtext()  
    public $recordcount;                     // int(4)  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Tag',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'strip' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'description' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT,
             'recordcount' =>  DB_DATAOBJECT_INT,
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
      $nb = $this->countTagged();
      if($nb>0) {
        trigger_error(__('Tag "%s" could not be deleted because %s records use it',array($this->__toString(),$nb)));
        return false;
      }
      return parent::delete();
    }
    public function countTagged()
    {
      return DB_DataObject_Plugin_Tag::countTagged();
    }
    public function decrementcount()
    {
      $this->recordcount--;
      $this->update();
    }
    public function incrementcount()
    {
      $this->recordcount++;
      $this->update();
    }    
}