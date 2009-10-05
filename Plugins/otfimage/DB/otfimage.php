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
    return array('atsize','pregenerateform','postprocessform','delete');
  }
  function preGenerateForm(&$fb,&$obj)
	{
		$obj->fb_preDefElements['filename']=& HTML_QuickForm::createElement('file',$obj->fb_elementNamePrefix.'filename'.$obj->fb_elementNamePostfix,$obj->fb_fieldsLabel['filename']);            
	}
	function postProcessForm(&$v,&$fb,&$obj)
	{
    $defs = $obj->_getPluginsDef();
		$field=$obj->fb_elementNamePrefix.'filename'.$obj->fb_elementNamePostfix;
		$obj->filename=$this->upFile($obj,'filename',$obj->fb_elementNamePrefix.'filename'.$obj->fb_elementNamePostfix,$defs['otfimage']['path']);
	}

	/**
	 * Helper method to store the file
	 */
	function upFile($obj, $field, $fieldName=null, $relativePathFromWebRoot){
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
  /**
   * @todo : file deletion
   */
	function delete(&$obj)
	{
		return;
	}
}	