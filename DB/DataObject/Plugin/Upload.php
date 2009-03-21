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

require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_Upload extends DB_DataObject_Plugin
{
    public $plugin_name='upload';

	function preGenerateForm(&$fb,&$obj)
	{
		$upFields=array_keys($obj->uploadFields);
		foreach($upFields as $k){
			$obj->fb_preDefElements[$k]=& HTML_QuickForm::createElement('file',$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix,$obj->fb_fieldsLabel[$k]);            
		}
	}
	function postGenerateForm(&$form,&$fb,&$obj)
	{
		foreach($obj->uploadFields as $k=>$v){
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
		return;
	}
	function prepareLinkedDataObject(&$linkedDataObject, $field,&$obj)
	{
		return;
	}
	function postProcessForm(&$v,&$fb,&$obj)
	{
		foreach($obj->uploadFields as $k=>$v){
			$field=$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix;
            if(key_exists('__upload_delete_'.$field,$_REQUEST)) {
                $file=SITE_URL.WWW_IMAGES_FOLDER.$v['path'].$obj->$k;
                @unlink($file);
                $obj->$k='';
                $obj->update();
            }
        }
		return;
	}
	function insert(&$obj)
	{
		foreach($obj->uploadFields as $k=>$v){
			$obj->$k=$this->upFile($obj,$k,$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix);
		}

	}
	function update(&$obj)
	{
		foreach($obj->uploadFields as $k=>$v){
			$obj->$k=$this->upFile($obj, $k, $obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix);
		}
	}
	
	function upFile($obj, $field, $fieldName=null){
		$info=$obj->uploadFields[$field];
	    if(is_null($fieldName)){
	        $fieldName=$field;
        }
		if (is_uploaded_file($_FILES[$fieldName]["tmp_name"])){
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
				if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field)
					&&chmod(IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field, 0644)){
						return $obj->$field;
				}
		}
		return $obj->$field;
	}
	function find($autoFetch=false,&$obj)
	{
		return;
	}
	function count(&$obj)
	{
	  return;
	}
	function delete(&$obj)
	{
		return;
	}
  function dateOptions($field, &$fb,&$obj) {
		return;
	}
}