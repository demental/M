<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   otfimagereceiver.php
 * @author       Arnaud Sellenet <demental at github>
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

class Plugins_Otfimagereceiver_db extends M_Plugin
{
  public function getEvents()
  {
    return array('getsecondaryimages','getmainimage','getimagenum','getallimages','newimage');
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
    return self::returnStatus($tbl);
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
    return self::returnStatus($tbl);
  }
	/**
	 * Retrieve the nth image (currently sorted by the mysql insertion date)
	 * @param int number
	 */
	public function getImageNum($num, DB_DataObject $obj)
	{
    $tbl = $this->_newImage($obj);
    $tbl->orderBy('ismain DESC');
    $tbl->limit($num-1,1);
    $tbl->find(true);
    return self::returnStatus($tbl);
  }
  /**
   * Returns a recordSet including all images linked to $obj
   * @return DB_DataObject
   */
  public function getAllImages(DB_DataObject $obj)
  {
    $tbl = $this->_newImage($obj);
    $tbl->find();
    return self::returnStatus($tbl);
  }
  /**
   * base method for getting recordset of images
   * @return DB_DataObject
   */
  public function newImage(DB_DataObject $obj)
  {
    return self::returnStatus($this->_newImage($obj));
  }
  protected function _newImage(DB_DataObject $obj) {
    $defs = $obj->_getPluginsDef();
    $tbl = DB_DataObject::factory($defs['otfimagereceiver']['table']);
    $tbl->record_table = $obj->tableName();
    $tbl->record_id = $obj->pk();
    return $tbl;
  }
}
