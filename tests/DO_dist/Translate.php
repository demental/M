<?php
/**
 * Table Definition for translate
 */
require_once 'M/DB/DataObject/Pluggable.php';

class DataObjects_Translate extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'translate';                       // table name
    public $id;                              // bigint(8)  primary_key not_null unsigned
    public $language;                        // char(2)  multiple_key not_null
    public $targettable;                     // varchar(30)  multiple_key not_null
    public $targetfield;                     // varchar(30)  multiple_key not_null
    public $record_id;                       // int(4)  multiple_key not_null unsigned
    public $translatedvalue;                 // text()  

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Translate',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'language' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'targettable' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'targetfield' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'record_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'translatedvalue' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT,
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
}
