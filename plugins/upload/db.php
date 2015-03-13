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
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

 if(!defined('TMP_PATH')){
 	define('TMP_PATH',ini_get('upload_tmp_dir'));
 }

class Plugins_Upload_DB extends M_Plugin
{
    public $plugin_name='upload';
  public function getEvents()
  {
    return array('pregenerateform','postgenerateform','preprocessform',
                  'delete','serve','getfileformat','getfilesize');
  }



	public function preGenerateForm(&$fb,&$obj)
	{
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];
		$upFields=array_keys($uploadFields);
		foreach($upFields as $k){
			$obj->fb_preDefElements[$k]= MyQuickForm::createElement('file',$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix,$obj->fb_fieldsLabel[$k]);
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
                    $label['note']='<a href="'.SITE_URL.WWW_IMAGES_FOLDER.$v['path'].$obj->$k.'">'.__('Download').'</a>';
                    $label['unit']='<input type="checkbox" name="__upload_delete_'.$field.'" value="1" />'.__('Delete');
                    $elt->setLabel($label);
				}
			}
		}
	}
	public function preProcessForm(&$values,$fb,$obj)
	{

	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];

		foreach($uploadFields as $k=>$v){
			$obj->$k=$this->upFile($obj, $k, $obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix);
		}
		foreach($uploadFields as $k=>$v){
			$field=$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix;
            if(key_exists('__upload_delete_'.$field,$_REQUEST)) {
                Log::info('Upload_Plugin : deletion was requested for field "'.$field.'"');
                $file=SITE_URL.WWW_IMAGES_FOLDER.'/'.$v['path'].'/'.$obj->$k;
                @unlink($file);
                $obj->$k='';
                $obj->update();
            }
        }
		return;
	}
	function upFile($obj, $field, $fieldName=null){
    Log::info('Upload_Plugin : starting upFile');
	  $uploadFields = $obj->_getPluginsDef();
	  $uploadFields = $uploadFields['upload'];

		$info=$uploadFields[$field];
	    if(is_null($fieldName)){
	        $fieldName=$field;
        }
		if (is_uploaded_file($_FILES[$fieldName]["tmp_name"])){
      Log::info('Upload_Plugin : file was uploaded in field '.$fieldName);
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
        Log::info('Upload_Plugin : Trying to move file to '.IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field);
				if (move_uploaded_file($_FILES[$fieldName]["tmp_name"], IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field)
					&&chmod(IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field, 0644)){
            Log::info('Upload_Plugin : Move OK, '.$field.' set to '.$obj->$field );
						return $obj->$field;
				} else {
          Log::error('Upload_Plugin : Move NOT OK');
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

  /**
   * Custom methods
   */
   public function getFileSize()
   {
     $args = func_get_args();
     if(is_a($args[0],'DB_DataObject')) {
       $obj = $args[0];
       $field = null;
     } else {
       $obj = $args[1];
       $field = $args[0];
     }
     Log::info('calling filesize for '.$field);
     $info = $obj->_getPluginsDef();
     $info = $info['upload'];
     if(is_null($field)) {
       $field = array_keys($info);
       $field = $field[0];
       $info = array_shift($info);

     } else {
       $info = $info[$field];
     }
     return self::returnStatus(FileUtils::getHumanFileSize(IMAGES_UPLOAD_FOLDER.'/'.$info['path'].'/'.$obj->{$field}));

   }
   public function getFileFormat()
   {
     $args = func_get_args();
     if(is_a($args[0],'DB_DataObject')) {
       $obj = $args[0];
       $field = null;
     } else {
       $obj = $args[1];
       $field = $args[0];
     }
     $info = $obj->_getPluginsDef();
     $info = $info['upload'];
     if(is_null($field)) {
       $field = array_keys($info);
       $field = $field[0];
       $info = array_shift($info);

     } else {
       $info = $info[$field];
     }
     return self::returnStatus(FileUtils::getFileExtension($obj->{$field}));
   }


   public function serve($field,$name,$obj)
   {
 	  $uploadFields = $obj->_getPluginsDef();
 	  $info = $uploadFields['upload'][$field];
 	  $filename = IMAGES_UPLOAD_FOLDER.$info['path'].$obj->$field;
     FileUtils::output($filename,$name);
     exit(0);
   }
}
