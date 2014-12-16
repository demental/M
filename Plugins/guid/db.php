<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_Guid
*/
/**
* M PHP Framework
*
* This plugin provides generation of hashed primary keys
* - convenient to prepare linked records before saving them to database
*
* @package      M
* @subpackage   DB_DataObject_Plugin_Guid
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class Plugins_Guid_Db extends M_Plugin
{
    public $plugin_name='guid';
    public function getEvents()
    {
      return array('insert','update','create_guid');
    }

    public function insert($obj) {
      $this->create_guid($obj);
      $obj->__new_with_id = false;
    }

    public function update($obj) {
      if($obj->__new_with_id) {
        $obj->insert();
        return false;
      }
    }
    public function create_guid($obj)
    {
      if(!$obj->pk()) {
        $obj->{$obj->pkName()} = Strings::create_guid();
        $obj->__new_with_id=1;
      }
    }
}
