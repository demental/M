<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   archiver.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Plugin that provides archiving features. Mostly used in office application 
 */


class DB_DataObject_Plugin_Archiver extends M_Plugin
{
  public function getEvents()
  {
    return array('find','getbatchmethods','batch_archiverarchive');
  }
  public function find($autofetch,$obj)
  {
    if(!$obj->archiver_archived) {
      $obj->whereAdd($obj->tableName().'.archiver_archived!=1');
    }
  }
  public function getbatchmethods($arr,$obj)
  {
    $arr['batch_archiverarchive']=array('title'=>('Put into archive'),'plugin'=>'archiver');
    return $this->returnStatus($arr);
  }
  public function batch_archiverarchive($obj)
  {
    while($obj->fetch()) {
      $obj->archiver_archived=1;
      $obj->update();      
    }
  }
}