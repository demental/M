<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_Images
*/
/**
* M PHP Framework
*
* Image upload/resize plugin
*
* @package      M
* @subpackage   DB_DataObject_Plugin_Images
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

 if(!defined('TMP_PATH')){
 	define('TMP_PATH',ini_get('upload_tmp_dir'));
 }

class DB_DataObject_Plugin_Images extends M_Plugin
{

  public $plugin_name='images';
  public function getEvents()
  {
    return array('pregenerateform','postgenerateform','preprocessform','postprocessform','delete');
  }
	function preGenerateForm(&$fb,&$obj)
	{
	  $info = $obj->_getPluginsDef();
    $info = $info['images'];
	  require_once 'HTML/QuickForm.php';
    HTML_QuickForm::registerElementType('imagefile','M/HTML/QuickForm/imagefile.php','HTML_QuickForm_imagefile');
		foreach($info as $k=>$v){
			$v=key_exists(0,$v)?$v[0]:$v;
      switch(true) {
        case $v['x']&&$v['y']:
			    $newSize = $v['x'].' X '.$v['y'].' pixels';
			    break;
			  case $v['x']&&!$v['y']:
			    $newSize = $v['x'].' '.__('pixels wide (proportions kept)');
			    break;
			  case !$v['x']&&$v['y'] :
			    $newSize = $v['y'].' '.__('pixels high (proportions kept)');
			    break;
			  default:
			    $newSize = '';
			    break;
      }
			$obj->fb_fieldLabels[$k] = array($obj->fb_fieldLabels[$k],'note'=>$newSize?'. '.__('Image will be resized to %s',array($newSize)):'');
      if(!empty($obj->$k)) {
        //TODO move this to postGenerateForm (like in upload plugin)
        $obj->fb_fieldLabels[$k]['unit']='<input type="checkbox" name="__image_delete_'.$k.'" value="1" />'.__('Delete current');
      }
			$obj->fb_preDefElements[$k]=& HTML_QuickForm::createElement('imagefile',$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix,$obj->fb_fieldLabels[$k],array('showimage'=>$v['showimage']),SITE_URL.WWW_IMAGES_FOLDER.$info[$k][0]['path'].'/');
		}
	}
	function postGenerateForm(&$form,&$fb,&$obj)
	{
    return;
	}
	function getUrl(&$obj,$field) {
    $info = $obj->_getPluginsDef();
    $info = $info['images'];
    $path = $info[$field][0]['path'];
    return SITE_URL.WWW_IMAGES_FOLDER.$path.'/'.$obj->$field;
	}
	function preProcessForm(&$values,&$fb,&$obj)
	{
	  $obj->fb_elementNamePrefix=$fb->elementNamePrefix;
    $obj->fb_elementNamePostfix=$fb->elementNamePostfix;
		return;
	}
	function postProcessForm(&$values,&$fb,&$obj)
	{
	  $info = $obj->_getPluginsDef();
    $info = $info['images'];
	  $upd=false;
		foreach($info as $k=>$val){
      $field = $obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix;
      if(key_exists('__image_delete_'.$field,$_REQUEST)) {
        $this->deletePhoto($obj,$k);
        $obj->$k='';
      }
		}
		foreach($info as $k=>$v){
			$obj->$k = $this->upPhoto($obj, $k, $obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix);
		}
    $obj->update();
		return;
	}
	function delete(&$obj)
	{
    $info = $obj->_getPluginsDef();
    $info = $info['images'];
    foreach($info as $field=>$params) {
      $this->deletePhoto($obj, $field);
    }
	}
 	function upPhoto(&$obj,$field,$fieldName=null)
 	{
    if(is_null($fieldName)) {
      $fieldName=$field;
    }
 		if(is_uploaded_file($_FILES[$fieldName]["tmp_name"])) {
 			$this->deletePhoto($obj, $field);
 			$this->move_file($obj, $field, $_FILES[$fieldName], true);
 		}
 		return $obj->$field;
 	}
  function move_file(&$obj, $field, $originalfile,$uploaded=false)
  {
    if($uploaded) {
  		$nom=explode(".",$originalfile['name']);
  		$ext=$nom[count($nom)-1];
      $obj->$field=$obj->tableName().'_'.$field.substr(md5(time()),0,6).rand(0,100).'.'.$ext;
      $res = move_uploaded_file($originalfile['tmp_name'], FileUtils::getFolderPath(TMP_PATH).$obj->$field);
    } else {
    	$nom=explode(".",$originalfile);
    	$ext=$nom[count($nom)-1];
      $obj->$field=$obj->tableName().'_'.$field.substr(md5(time()+rand(0,100)),0,6).'.'.$ext;
      $res = copy($originalfile, FileUtils::getFolderPath(TMP_PATH).$obj->$field);
    }
    if($res && chmod(FileUtils::getFolderPath(TMP_PATH).$obj->$field, 0644)) {
      $obj->say(__('regenerating thumbs'));
      $this->regenerateThumbs($obj, $field);
    }
  }
  function imageExists(&$obj,$field)
  {
    $info = $obj->_getPluginsDef();
    $info = $info['images'];
    if(!key_exists($field,$info)) {
      return false;
    }
    $file = IMAGES_UPLOAD_FOLDER.$info[$field][0]['path'].$obj->{$field};
    if(is_file($file)) {
      return true;
    }
    return false;
  }
 	function deletePhoto(&$obj,$field)
 	{
 	  $info = $obj->_getPluginsDef();
    $info = $info['images'];
 		$arr=key_exists(0,$info[$field])?$info[$field]:array($info[$field]);
 		foreach($arr as $k=>$v){
 			@unlink(IMAGES_UPLOAD_FOLDER.$v['path'].$obj->$field);
 			$obj->say(__('file deleted %s',array(IMAGES_UPLOAD_FOLDER.$v['path'].$obj->$field)));
 		}
 	}

	function regenerateThumbs(&$obj, $field){
	  $info = $obj->_getPluginsDef();
    $info = $info['images'];
		$arr=key_exists(0,$info[$field])?$info[$field]:array($info[$field]);
		require_once 'M/traitephoto.php';
		if(!file_exists(FileUtils::getFolderPath(TMP_PATH).$obj->$field)){
	    $original = $this->getBestImage($obj,$field);
	    if($original['isoriginal']){
	        unset($arr[$original['index']]);
	    }
			$photo=IMAGES_UPLOAD_FOLDER.FileUtils::getFolderPath($info[$field][$original['index']]['path']).$obj->$field;
      $firstRedim=false;
		} else {
			$photo=FileUtils::getFolderPath(TMP_PATH).$obj->$field;
      foreach($arr as $key=>$value) {
        if($value['original']) {
          $name = $this->_regenerateThumbUnit($photo,$value,$obj,$field);
          $obj->{$field}=$name;

    			$photo=IMAGES_UPLOAD_FOLDER.FileUtils::getFolderPath($value['path']).$name;
          unset($arr[$key]);
          break;
        }
      }
      $firstRedim=true;
		}
		foreach($info[$field] as $k=>$v){
      $name = $this->_regenerateThumbUnit($photo,$v,$obj,$field);
    }
		@unlink(FileUtils::getFolderPath(TMP_PATH).$obj->$field);
		return $name;
	}
	function _regenerateThumbUnit($photo,$v,&$obj,$field)
	{
	  $firstRedim=true;
	  if(!isset($v['x']) && !isset($v['y']) && !isset($v['maxx']) && !isset($v['maxy']) && !isset($v['overlay'])) {
		  copy($photo,IMAGES_UPLOAD_FOLDER.$v['path'].'/'.$obj->$field);
		  $name = $obj->$field;
	  } else {
  		$ph=new traitephoto;
  		$ph->photo=$photo;
  		$ph->path=IMAGES_UPLOAD_FOLDER.$v['path'];
  		$ph->nomsouhaite=$obj->$field;
  		$ph->qualite=$v['quality'];
  		$ph->width=$v['x'];
  		$ph->height=$v['y'];
  		$ph->maxx=$v['maxx'];
  		$ph->maxy=$v['maxy'];
  		if(isset($v['overlay']) && $firstRedim) {
        $type='png';
        $ph->path=FileUtils::getFolderPath(TMP_PATH);
        $ph->nomsouhaite='overlaytemp.png';
  		} else {
  	    $type = $v['type']?$v['type']:null;
  		}
  		$ph->redim();
  		$name = $ph->sauvegarde($type);

  		if(isset($v['overlay']) && $firstRedim) {
  	    require_once 'Image/Canvas.php';
  	    require_once 'Image/Transform.php';
        $source = $v['path'].'/'.$name;
  			$infosSource = & Image_Transform::factory('GD');
        if(PEAR::isError($infosSource)) {
            print_r($infosSource);
        }
  			$tmp=FileUtils::getFolderPath(TMP_PATH);
  			$infosSource->load($tmp.'overlaytemp.png');
  			$infosOver = & Image_Transform::factory('GD');
  			$infosOver->load(APP_ROOT.$v['overlay'][0]);
  			$opts=array('width'=>$infosSource->getImageWidth(),'height'=>$infosSource->getImageHeight(),'transparent'=>true);
  			$img = & Image_Canvas::factory('png',$opts);
  			$img->image(array('filename'=>TMP_PATH.'overlaytemp.png','x'=>0,'y'=>0));

  			switch($v['overlay']['position']) {
  				case 'top-left':$x=0;$y=0;break;
  				case 'top-right':$x=$infosSource->getImageWidth()-$infosOver->getImageWidth();$y=0;break;
  				case 'bottom-left':$x=0;$y=$infosSource->getImageHeight()-$infosOver->getImageHeight();break;
  				case 'bottom-right':$x=$infosSource->getImageWidth()-$infosOver->getImageWidth();$y=$infosSource->getImageHeight()-$infosOver->getImageHeight();break;
  				default:$x=0;$y=0;break;
  			}
  			$img->image(array('filename'=>APP_ROOT.$v['overlay'][0],'x'=>$x,'y'=>$y));
    		$img->save(array('filename'=>$tmp.'overlaytemp.png'));

  			$ph=new traitephoto;
  			$ph->photo=$tmp.'overlaytemp.png';
  			$ph->path=IMAGES_UPLOAD_FOLDER.$v['path'];
  			$ph->nomsouhaite=$obj->$field;
  			$ph->qualite=$v['quality'];
  			$ph->redim();
  			$name = $ph->sauvegarde($v['type']?$v['type']:null);
  			unset($ph);
  		}
    }
		if($name!=$obj->$field) {
		    @unlink(IMAGES_UPLOAD_FOLDER.$v['path'].'/'.$obj->$field);
		}
		unset($ph);
    return $name;
	}
	function getBestImage(&$obj, $field)
	{
	  $info = $obj->_getPluginsDef();
    $info = $info['images'];
		$arr=key_exists(0,$info[$field])?$info[$field]:array($info[$field]);
		$maxsurface=0;$maxid=null;
		foreach($arr as $k=>$v){
      if(key_exists('original',$v)) {
        return array('original'=>true,'index'=>$k);
      }
			$surface=$v['x']+$v['y'];
			if($maxsurface<$surface) {
				$maxid=$k;
				$maxsurface=$surface;
			}
		}
		return array('original'=>false, 'index'=>$maxid);
	}
}