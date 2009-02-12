<?php
// ============================================================
// = This so-called officepack plugin provides two features :
// = * Generation of hashed primary keys
// = * If the field "deleted" is present in the table, use it as a flag for deletion
// = Handy for apps where it is necessary sometimes to retreive deleted records.
// = Also handy for apps with many sources (like smartphones) as hashed PKs allows synchronization.
// = This was inspired from sugarCRM
// ============================================================

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