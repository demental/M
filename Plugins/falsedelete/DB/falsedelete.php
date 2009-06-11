<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_FalseDelete
*/
/**
* M PHP Framework
*
* This plugin just overrides the deletion behaviour :
* instead of deleting a record, it marks it as deleted.
*
* @package      M
* @subpackage   DB_DataObject_Plugin_FalseDelete
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class DB_DataObject_Plugin_FalseDelete extends M_Plugin
{
  public $plugin_name='falseDelete';
  public function getEvents()
  {
    return array('delete','find','count');
  }
  function delete($obj) {
    $db = $obj->getDatabaseConnection();
    $db->exec('UPDATE '.$db->quoteIdentifier($obj->tableName()).' SET '.$db->quoteIdentifier('deleted').' = 1');
    return 'bypass';
  }
  function find($autoFetch,$obj) {
    $obj->whereAdd($obj->tableName().'.deleted!=1');
  }
  function count($obj) {
    $obj->whereAdd($obj->tableName().'.deleted!=1');
  }
}