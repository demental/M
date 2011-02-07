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

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class DB_DataObject_Plugin_OfficePack extends M_Plugin
{
    public $plugin_name='officePack';
    public function getEvents()
    {
      return array('delete','insert','find','update','count');
    }
    function delete($obj) {
      if(key_exists('deleted',$obj->table())) {
        $db = $obj->getDatabaseConnection();
        $db->exec('UPDATE '.$db->quoteIdentifier($obj->tableName()).' SET '.$db->quoteIdentifier('deleted').' = 1 WHERE id='.$db->quote($obj->id));
        return 'bypass';
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