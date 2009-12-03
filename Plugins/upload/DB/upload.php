<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_Upload
*/
/**
* M PHP Framework
*
* File upload handling plugin
*
* @package      M
* @subpackage   DB_DataObject_Plugin_Upload
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

 if(!defined('TMP_PATH')){
 	define('TMP_PATH',ini_get('upload_tmp_dir'));
 }

class DB_DataObject_Plugin_Upload extends M_Plugin
{
    public $plugin_name='upload';
  public function getEvents()
  {
    return array('pregenerateform','postgenerateform','preprocessform','postprocessform','preparelinkeddataobject',
                  'insert','update','delete','serve');
  }

  public function serve($field,$name,$obj)
  {
	  $uploadFields = $obj->_getPluginsDef();
	  $info = $uploadFields['upload'][$field];
	  $filename = IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field;
    FileUtils::output($filename,$name);
    die();
  }

	public function preGenerateForm(&$fb,&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];
		$upFields=array_keys($uploadFields);
		foreach($upFields as $k){
			$obj->fb_preDefElements[$k]=& HTML_QuickForm::createElement('file',$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix,$obj->fb_fieldsLabel[$k]);            
		}
	}
	function postGenerateForm(&$form,&$fb,&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];

		foreach($uploadFields as $k=>$v){
			$field=$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix;
			if($form->elementExists($field)){
				if(!empty($obj->$k)){
					$elt=& $form->getElement($field);
					$label=$elt->getLabel();
					if(!is_array($label)){
						$label=array($label);
					}
                    $label['note']='<a href="'.SITE_URL.WWW_IMAGES_FOLDER.$v['path'].$obj->$k.'">'.__('Voir la version actuelle').'</a>';
                    $label['unit']='<input type="checkbox" name="__upload_delete_'.$field.'" value="1" />'.__('Supprimer la version actuelle');
                    $elt->setLabel($label);
				}
			}
		}
	}
	function preProcessForm(&$values,&$fb,&$obj)
	{
	  $obj->fb_elementNamePrefix=$fb->elementNamePrefix;
    $obj->fb_elementNamePostfix=$fb->elementNamePostfix;
		return;
	}
	function prepareLinkedDataObject(&$linkedDataObject, $field,&$obj)
	{
		return;
	}
	function postProcessForm(&$v,&$fb,&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];
	  
		foreach($uploadFields as $k=>$v){
			$field=$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix;
            if(key_exists('__upload_delete_'.$field,$_REQUEST)) {
                $file=SITE_URL.WWW_IMAGES_FOLDER.'/'.$v['path'].'/'.$obj->$k;
                @unlink($file);
                $obj->$k='';
                $obj->update();
            }
        }
		return;
	}
	function insert(&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];
	  
		foreach($uploadFields as $k=>$v){
			$obj->$k=$this->upFile($obj,$k,$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix);
		}

	}
	function update(&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];
	  
		foreach($uploadFields as $k=>$v){
			$obj->$k=$this->upFile($obj, $k, $obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix);
		}
	}
	
	function upFile($obj, $field, $fieldName=null){
    Log::info('starting upFile');
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];

		$info=$uploadFields[$field];
	    if(is_null($fieldName)){
	        $fieldName=$field;
        }
		if (is_uploaded_file($_FILES[$fieldName]["tmp_name"])){
      Log::info('file is uploaded');
				@unlink(IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field);
				$obj->$field = $_FILES[$fieldName]["name"];
				if(key_exists('nameField',$info)){
					$obj->$info['nameField']=$obj->$field;
				}
				$nom=explode(".",$obj->$field);
				$ext=$nom[count($nom)-1];
				if(key_exists('formatField',$info)){
					$obj->$info['formatField']=$ext;
				}
				$obj->$field=$obj->tableName().'_'.$field.substr(md5(time()+rand(0,100)),0,6).".".$ext;
        Log::info('Trying to move file to '.IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field);
				if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field)
					&&chmod(IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field, 0644)){
            Log::info('Move OK, '.$field.' set to '.$obj->$field );
						return $obj->$field;
				} else {
          Log::error('Move NOT OK');
				}

		}
		return $obj->$field;
	}
  /**
   * @todo : file deletion
   */
	function delete(&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];
		foreach($uploadFields as $k=>$v){
	    $file=IMAGES_UPLOAD_FOLDER.'/'.$v['path'].'/'.$obj->$k;
      @unlink($file);
    }
		return;
	}
}