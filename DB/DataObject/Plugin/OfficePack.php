<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_OfficePack
*/
/**
* M PHP Framework
*
* This so-called officepack plugin provides two features :
* - Generation of hashed primary keys
* - If the field "deleted" is present in the table, use it as a flag for deletion
* Handy for apps where it is necessary sometimes to retreive deleted records.
* Also handy e.g. smartphones synchronization as hashed PKs avoids index conflicts if database is grown from various sources.
* This was inspired from sugarCRM
*
* @package      M
* @subpackage   DB_DataObject_Plugin_OfficePack
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once "M/DB/DataObject/Plugin.php";

class DB_DataObject_Plugin_OfficePack extends DB_DataObject_Plugin
{
    public $plugin_name='officePack';

    function delete($obj) {
      if(key_exists('deleted',$obj->table())) {
        $obj->deleted=1;
        $obj->update();
        return false;
      }
    }
    function insert($obj) {
      $this->create_guid($obj);
      $obj->__new_with_id = false;
    }
    function find($autoFetch,$obj) {
      if(key_exists('deleted',$obj->table())) {
        $obj->whereAdd($obj->tableName().'.deleted!=1');
      }
    }
    function count($obj) {
      if(key_exists('deleted',$obj->table())) {
        $obj->whereAdd($obj->tableName().'.deleted!=1');
      }
    }
    function update($obj) {
      if($obj->__new_with_id) {
        $obj->insert();
        return false;
      }
    }
    function create_guid($obj)
    {
      if(empty($obj->id)) {
        $obj->id = Strings::create_guid();
        $obj->__new_with_id=1;

      }
    }
}