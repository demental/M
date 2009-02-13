<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

/**
* M PHP Framework
* @package      M
* @subpackage   FileUtils
*/
/**
* M PHP Framework
*
* Misc utilities related to files/folders manipulations
*
* @package      M
* @subpackage   FileUtils
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class fileUtils{
    public $_pattern='^\.$|^\.\.$';
    function __construct($file) {
        if(is_file($file)) {
            $this->file = $file;
        } else {
            $this->file = $this->getFolderPath($file);
        }
    }
    function addExcludeExtension($ext) {
        $this->addExcludePattern('.+\.'.$ext.'$');
    }
    function addExcludePattern($pattern) {
        $this->_pattern .= '|'.$pattern;
    }
	function is_Image($file){
		return in_array(fileUtils::getFileExtension($file),array('jpg','jpeg','gif','png'));
	}
	function getFileExtension($file){
		$nom=explode(".",$file);
		$ext=$nom[count($nom)-1];
		return strtolower($ext);
	}
	function getFilesOlderThan($time,$absolute=false) {
        $t = time()-$time;
	    
        foreach($this->getContents() as $file) {
            if(filemtime($this->file.$file)<$t) {
                $out[]=$absolute?$this->file.$file:$file;
            }

        }
        return $out;
	}
	function deleteFilesOlderThan($time) {
        foreach($this->getFilesOlderThan($time) as $file) {
            unlink($this->file.$file);
        }
	}
	public static function getFiles($folder)
	{
    if(!is_dir($folder)) {
        return array();
    }
    $out = array();
    if ($dh = @opendir($folder)) {
			while (($file = readdir($dh)) !== false)
			{
          if(!is_file($folder.$file)) continue;
          
          $out[]=$file;

      }
    }
	    return $out;
	}
  public static function getAllFiles($folder,$extensionfilter='')
  {
    if(!is_array($extensionfilter)) {
      $extensionfilter = array($extensionfilter);
    }
    if(!ereg('\/$',$folder)) {
      $folder.='/';
    }
    if(!is_dir($folder)) {
        return array();
    }
    $out = array();
    if ($dh = @opendir($folder)) {
			while (($file = readdir($dh)) !== false)
			{
			  if(ereg('^\.',$file)) continue;
        if(is_dir($folder.$file)) {
          $out = array_merge($out,self::getAllFiles($folder.$file,$extensionfilter));
        } elseif(!is_file($folder.$file)) {
          continue;
        } else {
          if(empty($extensionfilter) || eregi('\.'.implode('|',$extensionfilter),$file)) {
            $out[]=$folder.$file;          
          }
        }

      }
      sort($out);
	    return $out;

    }
  }
  public static function hasFiles($folder)
  {
    if(!is_dir($folder)) {
        return false;
    }
    if ($dh = @opendir($folder)) {
			while (($file = readdir($dh)) !== false)
			{
          if(!is_file($folder.$file)) continue;
          return true;

      }
    }
	    return false;
  }

  function getContents() {
        $out=array();
        if(!is_dir($this->file)) {
            return false;
        }
        if ($dh = @opendir($this->file))
		{
			while (($file = readdir($dh)) !== false)
			{
                if(!preg_match('`'.$this->_pattern.'`',$file)) {
                    $out[]=$file;
                }
            }
        }
	    return $out;
    }
	function deleteContents() {
        
	    if ($dh = @opendir($this->file))
		{
            foreach($this->getContents() as $file) {
                    unlink($this->file.$file);
                    $nbd++;
            }
        }
        return $nbd;
	}
	public function output($file)
	{
		switch(fileUtils::getFileExtension($file)){
			case 'jpeg':
			case 'jpg':
        $ctype='image/jpeg';
        break;
			case 'gif':
        $ctype='image/gif';
        break;
			
			case 'png':      
        $ctype='image/gif';
        break;
			default:
			return;
		}
    header('Content-Type:'.$ctype);
    readfile($file);
    exit;
	}
	function toHtml($file){
		switch(fileUtils::getFileExtension(IMAGES_UPLOAD_FOLDER.$file)){
			case 'jpeg':
			case 'jpg':
			case 'gif':
			case 'png':
				return '<img src="'.WWW_IMAGES_FOLDER.$file.'" />';
			break;
			case 'swf':
				require_once 'misc/lib-swf.inc.php';
				$buff=file_get_contents(IMAGES_UPLOAD_FOLDER.$file);
				$size=phpAds_SWFDimensions($buff);
				$output='<!--[if IE]>
				 <object type="application/x-shockwave-flash" data="'.WWW_IMAGES_FOLDER.$file.'" width="'.$size[0].'" height="'.$size[1].'" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">
				 <param name="movie" value="'.WWW_IMAGES_FOLDER.$file.'" />
				 </object>
				<![endif]-->
				<!--[if !IE]> <-->
				 <object type="application/x-shockwave-flash" data="'.WWW_IMAGES_FOLDER.$file.'" width="'.$size[0].'" height="'.$size[1].'">
				 <param name="movie" value="'.WWW_IMAGES_FOLDER.$file.'" />
					</dl> </object>
				<!--> <![endif]-->';
				return $output;
			break;
			default:
				return '<a href="'.$file.'">Voir le fichier</a>';
			break;
		}
	}
	function getFolderPath($path){
	    return ereg('/$',$path)?$path:$path.'/';
	}	
	public static function delete($path)
	{
    if(is_file($path)) {
      unlink($path);
    } else {
      exec('rm -Rf '.$path);
    }
	}
}