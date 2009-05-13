<?php
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

/**
 *
 * Misc utilities related to files/folders manipulations
 * This class can be instanciated for some operations, useful if you want to work
 * on a specific set of files.
 *
 */
class fileUtils
{

	/**
	 *
	 * Pattern regular expression
	 *
	 * @var		string
	 * @access	public
	 */
	public $_pattern='^\.$|^\.\.$';

	/**
	 *
	 * Constructor
	 *
	 * @param	$file	string	File URL
	 * @return	string
	 */
	function __construct($file)
	{
		if(is_file($file)) {
			$this->file = $file;
		} else {
			$this->file = $this->getFolderPath($file);
		}
	}

	/**
	 *
	 * Add extension to exclude pattern
	 *
	 * @param $ext	string	Extension to add
	 */
	public function addExcludeExtension($ext)
	{
		$this->addExcludePattern('.+\.'.$ext.'$');
	}

	/**
	 *
	 * Add exclude pattern
	 *
	 * @param	$pattern	string	Pattern to exclude
	 */
	public function addExcludePattern($pattern) 
	{
		$this->_pattern .= '|'.$pattern;
	}

	/**
	 *
	 * Check if file is an image
	 *
	 * @param $file	string	File URL
	 * @return boolean
	 */
	public static function is_Image($file)
	{
		return in_array(fileUtils::getFileExtension($file),array('jpg','jpeg','gif','png'));
	}

	/**
	 *
	 * Get the file extension
	 *
	 * @param $file	string	File name/URL
	 * @return extension	string
	 */
	function getFileExtension($file)
	{
		$nom=explode(".",$file);
		$ext=$nom[count($nom)-1];
		return strtolower($ext);
	}

	/**
	 *
	 * Get files older than a given date
	 *
	 * @param	$time		unix timestamp
	 * @param	$absolute	boolean
	 * @return	Files		array
	 */
	public function getFilesOlderThan($time,$absolute=false)
	{
		$t = time()-$time;
			
		foreach($this->getContents() as $file) {
			if(filemtime($this->file.$file)<$t) {
				$out[]=$absolute?$this->file.$file:$file;
			}

		}
		return $out;
	}

	/**
	 *
	 * Delete files older than a given date
	 *
	 * @param $time		unix timestamp
	 */
	public function deleteFilesOlderThan($time)
	{
		foreach($this->getFilesOlderThan($time) as $file) {
			unlink($this->file.$file);
		}
	}
  /**
   * Check if a file exists in the include path
   *
   * @version     1.2.1
   * @author      Aidan Lister <aidan@php.net>
   * @link        http://aidanlister.com/repos/v/function.file_exists_incpath.php
   * @param       string     $file       Name of the file to look for
   * @return      mixed      The full path if file exists, FALSE if it does not
   */
  public static function file_exists_incpath ($file)
  {
    if(ereg('^'.DIRECTORY_SEPARATOR,$file)) {
      return file_exists($file);
    }
    $paths = explode(PATH_SEPARATOR, get_include_path());

    foreach ($paths as $path) {
      // Formulate the absolute path

      $fullpath = $path . DIRECTORY_SEPARATOR . $file;
      // Check it
      if (file_exists($fullpath)) {
        return $fullpath;
      }
    }
 
    return false;
  }
	/**
	 *
	 * Get Files list of a given folder
	 *
	 * @access public
	 * @static
	 * @param	$folder		string	Source folder
	 * @return	Files list	array
	 */
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

	/**
	 *
	 * Get Files list of a given folder with extension filtering capabilities
	 *
	 * @access	public
	 * @static
	 * @param	$folder				string	Source folder
	 * @param	$extensionfilter	string	Extension to filter
	 * @return	Files list			array
	 */
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

	/**
	 *
	 * Check if directory contains files
	 *
	 * @param $folder	string	Source folder
	 * @return boolean
	 */
	function hasFiles($folder)
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

	/**
	 *
	 * Get directory content
	 *
	 * @return Files list
	 */
	function getContents() 
	{
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

	/**
	 *
	 * Delete directory content
	 *
	 * @return Total of directory
	 */
	function deleteContents() 
	{

		if ($dh = @opendir($this->file))
		{
			foreach($this->getContents() as $file) {
				unlink($this->file.$file);
				$nbd++;
			}
		}
		return $nbd;
	}

	/**
	 *
	 * Output specified file
	 *
	 * @access	public
	 * @param	$file	string	File URL
	 */
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
	/**
	 *
	 * Generate HTML Tag for specified file
	 *
	 * @param	$file	string	File URL
	 * @return	HTML Output		string
	 */
	function toHtml($file)
	{
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
				return '<a href="'.$file.'">'.__('View file').'</a>';
				break;
		}
	}

	/**
	 *
	 * description
	 *
	 * @param $path
	 * @return unknown_type
	 */
	function getFolderPath($path)
	{
		return ereg('/$',$path)?$path:$path.'/';
	}

	/**
	 *
	 * description
	 *
	 * @param $path
	 * @return unknown_type
	 */
	public static function delete($path)
	{
		if(is_file($path)) {
			unlink($path);
		} else {
			exec('rm -Rf '.$path);
		}
	}
}