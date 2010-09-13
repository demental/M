<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   otfimage.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * 'otf' stands for "On-the-fly"
 * This plugin allows to set a table as image attachments table.
 * Works with the plugin otfimagereceiver
 */


class DB_DataObject_Plugin_Otfimage extends M_Plugin
{
  public function getEvents()
  {
    return array('getowner','defaultimage','attachto','setasmain','atsize','pregenerateform','postprocessform','delete');
  }
  public function preGenerateForm(&$fb,&$obj)
	{
		$obj->fb_preDefElements['filename']=& HTML_QuickForm::createElement('file',$obj->fb_elementNamePrefix.'filename'.$obj->fb_elementNamePostfix,$obj->fb_fieldsLabel['filename']);            
	}
	public function postProcessForm(&$v,&$fb,&$obj)
	{

    $defs = $obj->_getPluginsDef();
		$field=$obj->fb_elementNamePrefix.'filename'.$obj->fb_elementNamePostfix;
	  if(!$_FILES[$field]['tmp_name']) return;

    $filename = $obj->getImageName();
    if(!$filename) {
      $filename = $obj->getOwner()->tableName().'_'.substr(md5(time()+rand(0,100)),0,10);
    }
		$obj->filename=$this->_upFile($obj,$obj->fb_elementNamePrefix.'filename'.$obj->fb_elementNamePostfix,$defs['otfimage']['path'],$filename);
    $obj->update();
    /**
     * Clearing cache for this image
     **/ 
     $filename = eregi_replace('(\.[^\.]+)$','',basename($obj->filename));
     $cachefolder = APP_ROOT.WEB_FOLDER.'/'.$defs['otfimage']['cache'].'/'.$filename.'/';
     foreach(FileUtils::getAllFiles($cachefolder) as $file) {
       @unlink($file);
     }
     /**
      * Setting as main if none exist
      */

      $main = $obj->getOwner();
      $mainImg = $main->getMainImage();
      
      
      if(!$obj->ismain && !$mainImg->pk()) {
        $obj->setAsMain();
      }

  	}

  /**
   * @todo : cached files deletion
   */
	function delete($obj)
	{
	  @unlink($this->_getOriginalPath($obj));
    // CHecking if main is deleted, take arbitraty record to set as main
    if($obj->ismain) {
      $img = $obj->getOwner()->newImage();
      $img->whereAdd($obj->pkName().' != '.$obj->getDatabaseConnection()->quote($obj->id));
      $img->limit(0,1);
      if($img->find(true)) {
        $img->setAsMain();
      }
    }
		return;
	}
	public function parseParams($string)
	{
	  if(is_array($string)) {
	    return $string;
	  }
	  $tmpparams = explode(',',$string);
    foreach($tmpparams as $apar) {
      list($key,$value) = explode(':',$apar);
      if($value) {
        $params[$key] = $value;
      } elseif($key) {
        $params['maxx'] = $key;
      }
    }
    if(!$params) {
      $params = array('keeporiginal'=>'');
    }
    return $params;
	}
	public function atSize($params = null,$obj)
	{
    $defs = $obj->_getPluginsDef();
    $params = $this->parseParams($params);
    $filename = preg_replace('`(\.[^\.]+)$`','',basename($obj->filename));
    ksort($params);
    $paramskey = $this->paramstostring($params);

    $params['format'] = $params['format']?$params['format']:FileUtils::getFileExtension($obj->filename);
    $cachefolder = $defs['otfimage']['cache'].'/'.$filename.'/';
    @mkdir(APP_ROOT.WEB_FOLDER.'/'.$cachefolder);
    $cachename = $cachefolder.$paramskey.'.'.($params['format']);
    $cachefile = APP_ROOT.WEB_FOLDER.'/'
                .$cachename;

    $cacheurl = '/'.$cachename;
    if(file_exists($this->_getOriginalPath($obj)) && is_file($this->_getOriginalPath($obj))) {
      if (!file_exists($cachefile)) {
        $this->_createResized($this->_getOriginalPath($obj),$cachefile,$params);
      }
    } else {
      return $this->returnStatus(null);
    }
    return $this->returnStatus($cacheurl);
  }
  public function paramstostring($params)
  {
    $out='';
    foreach($params as $k=>$aparam) {
      if($k=='format') continue;
      $out.=$k.'-'.$aparam;
    }
    return $out;
  }
  public function getOwner($obj)
  {
    if(!$obj->record_table) return $this->returnStatus($obj);
    $return = DB_DataObject::factory($obj->record_table);
    $return->{$return->pkName()} = $obj->record_id;
    $return->find(true);
    return $this->returnStatus($return);
  }
  public function attachTo(DB_DataObject $owner,$obj)
  {
    $obj->record_table = $owner->tableName();
    $obj->record_id = $owner->pk();
    $obj->update();
  }
  
  public function setAsMain($obj)
  {
    $obj->ismain=1;
    $others = DB_DataObject::factory($obj->tableName());
    $others->ismain=1;
    $others->record_table = $obj->record_table;
    $others->record_id = $obj->record_id;
    $others->find();
    while($others->fetch()) {
      $others->ismain=0;
      $others->update();
    }
    $obj->update();
  }
  /**
   * Protected helper methods
   */
	/**
	 * Helper method to store the file
	 */
	protected function _upFile($obj, $field, $relativePathFromUploadFolder,$prefix = null){
    if(is_null($prefix)) {
      $prefix = $obj->tableName();
    }
		if (is_uploaded_file($_FILES[$field]['tmp_name'])){
     $ext = FileUtils::getFileExtension($_FILES[$field]['name']);
		 $name = $prefix.".".$ext;
     $destination = IMAGES_UPLOAD_FOLDER.$relativePathFromUploadFolder.'/'.$name;

			if (move_uploaded_file($_FILES[$field]["tmp_name"], $destination)
				&&chmod($destination, 0644)){
				return $name;
			}
		}
		return false;
	}
  protected function _getOriginalPath($obj)
  {
    if(preg_match('`^\/`',$obj->filename)) {
      return APP_ROOT.WEB_FOLDER.$obj->filename;
    }
    $defs = $obj->_getPluginsDef();
    $res = IMAGES_UPLOAD_FOLDER.$defs['otfimage']['path'].'/'.$obj->filename;

    return $res;
  }
  public function defaultImage($default,$obj)
  {
    if(empty($obj->filename)) {
      $obj->filename = $default;
    }
    return $this->returnStatus($obj);
  }
  protected function _createResized($original,$destination,$params)
  {

	  if(!isset($params['x']) && !isset($params['y']) && !isset($params['maxx']) && !isset($params['maxy'])) {
		  copy($original,$destination);
	  } else {
	    require_once 'M/traitephoto.php'; 
  		$ph=new traitephoto;
  		$ph->photo=$original;
  		$ph->path=dirname($destination);
  		$ph->nomsouhaite=basename($destination);
  		$ph->qualite=$params['q'];
  		$ph->width=$params['x'];
  		$ph->height=$params['y'];
  		$ph->maxx=$params['maxx'];
  		$ph->maxy=$params['maxy'];
  		$ph->redim();
  		$name = $ph->sauvegarde($params['format']);
    }
    return;
	}
}	