<?php
/**
 * Table Definition for title
 */
require_once 'M/DB/DataObject/Pluggable.php';

class DataObjects_Title extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'title';                           // table name
    public $id;                              // int(4)  primary_key not_null unsigned
    public $album_id;                        // int(4)   not_null unsigned
    public $ordre;                           // int(4)   not_null
    public $title;                           // varchar(255)   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Title',$k,$v); }

    function table()
    {
         return array(
             'id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'album_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'ordre' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'title' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
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
			'album_id'=>'album:id',

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
