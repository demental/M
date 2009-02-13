<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

/**
* M PHP Framework
* @package      M
* @subpackage   tests
*/
/**
* M PHP Framework
*
* DB_DataObject used for unit testing
*
* @package      M
* @subpackage   tests
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
 * Table Definition for album_i18n
 */
require_once 'M/DB/DataObject/Pluggable.php';

class DataObjects_Album_i18n extends DB_DataObject_Pluggable 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'album_i18n';                      // table name
    public $i18n_id;                         // int(4)  primary_key not_null unsigned
    public $i18n_record_id;                  // int(4)  multiple_key not_null unsigned
    public $i18n_lang;                       // char(2)  multiple_key not_null
    public $description;                     // mediumtext()   not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Album_i18n',$k,$v); }

    function table()
    {
         return array(
             'i18n_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'i18n_record_id' =>  DB_DATAOBJECT_INT + DB_DATAOBJECT_NOTNULL,
             'i18n_lang' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_NOTNULL,
             'description' =>  DB_DATAOBJECT_STR + DB_DATAOBJECT_TXT + DB_DATAOBJECT_NOTNULL,
         );
    }

    function keys()
    {
         return array('i18n_id');
    }

    function sequenceKey() // keyname, use native, native name
    {
         return array('i18n_id', true, false);
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
			'i18n_record_id'=>'album:id',

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
