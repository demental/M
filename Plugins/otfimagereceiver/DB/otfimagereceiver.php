<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   otfimagereceiver.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * 'otf' stands for "On-the-fly"
 * This plugin allows to attach images to a recordSet.
 * Works with the plugin otfimage
 */
if(!defined('TMP_PATH')){
 	define('TMP_PATH',ini_get('upload_tmp_dir'));
}

class DB_DataObject_Plugin_Otfimagereceiver extends M_Plugin
{
  public function getEvents()
  {
    return array('getsecondaryimages','getmainimage','getallimages','newimage');
  }
  
  /**
   * Returns a recordSet of images linked to $obj that are not set as main image
   * @return DB_DataObject
   */
  public function getSecondaryImages(DB_DataObject $obj)
  {
    $tbl = $this->_newImage($obj);
    $tbl->ismain=0;
    $tbl->find();
    return $this->returnStatus($tbl);
  }
  
  /**
   * Returns the prefetched record linked to $obj and set as main image
   * @return DB_DataObject
   */
  public function getMainImage(DB_DataObject $obj)
  {
    $tbl = $this->_newImage($obj);
    $tbl->ismain=1;
    $tbl->find(true);
    return $this->returnStatus($tbl);
  }
  /**
   * Returns a recordSet including all images linked to $obj
   * @return DB_DataObject
   */
  public function getAllImages(DB_DataObject $obj)
  {
    $tbl = $this->_newImage($obj);
    $tbl->find();
    return $this->returnStatus($tbl);
  }
  /**
   * base method for getting recordset of images
   * @return DB_DataObject
   */
  public function newImage(DB_DataObject $obj)
  {
    return $this->returnStatus($this->_newImage($obj));
  }
  protected function _newImage(DB_DataObject $obj) {
    $defs = $obj->_getPluginsDef();
    $tbl = DB_DataObject::factory($defs['otfimagereceiver']['table']);
    $tbl->record_table = $obj->tableName();
    $tbl->record_id = $obj->pk();
    return $tbl;
  }
}  