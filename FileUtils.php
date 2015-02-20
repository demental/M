<?php
/**
 * M PHP Framework
 *
 * Misc utilities related to files/folders manipulations
 *
 * @package      M
 * @subpackage   FileUtils
 * @author       Arnaud Sellenet <demental@sat2way.com>
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
class FileUtils
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
			$this->file = self::ensure_trailing_slash($file);
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
    if(strpos($file,DIRECTORY_SEPARATOR) === 0) {
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
	 * Get Subfolders list of a given folder
	 *
	 * @access public
	 * @static
	 * @param	$folder		string	Source folder
	 * @return	Files list	array
	 */
	public static function getFolders($folder)
	{
		if(!is_dir($folder)) {
			return array();
		}
		$out = array();
		if ($dh = @opendir($folder)) {
			while (($file = readdir($dh)) !== false)
			{
				if(is_file($folder.$file)) continue;

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
	 * @param $recursive bool recursively scan folders (equivalent to ls -R)
	 * @return	Files list			array
	 */
	public static function getAllFiles($folder,$extensionfilter='',$recursive = true)
	{
		if(!is_array($extensionfilter)) {
			$extensionfilter = array($extensionfilter);
		}
		$folder = self::ensure_trailing_slash($folder);
		if(!is_dir($folder)) {
			return array();
		}
		$out = array();
		if ($dh = @opendir($folder)) {
			while (($file = readdir($dh)) !== false)
			{
				if(self::is_dot_file($file)) continue;
				if(is_dir($folder.$file) && $recursive) {
					$out = array_merge($out,self::getAllFiles($folder.$file,$extensionfilter));
				} elseif(!is_file($folder.$file)) {
					continue;
				} else {
					if(empty($extensionfilter[0]) || preg_match('`\.'.implode('|',$extensionfilter).'`',$file)) {
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
	 * Get Files list of a given folder with extension filtering capabilities
	 * PLUS folders
	 * @access	public
	 * @static
	 * @param	$folder				string	Source folder
	 * @param	$extensionfilter	string	Extension to filter
	 * @return	Files list			array
	 */
	public static function getAll($folder,$extensionfilter='')
	{
		if(!is_array($extensionfilter)) {
			$extensionfilter = array($extensionfilter);
		}
		$folder = self::ensure_trailing_slash($folder);
		if(!is_dir($folder)) {
			return array();
		}
		$out = array();
		if ($dh = @opendir($folder)) {
			while (($file = readdir($dh)) !== false)
			{
				if(self::is_dot_file($file)) continue;
				if(is_dir($folder.$file)) {
					$out = array_merge($out,self::getAllFiles($folder.$file,$extensionfilter),array($folder.$file));
				} elseif(!is_file($folder.$file)) {
					continue;
				} else {
					if(empty($extensionfilter[0]) || preg_match('`\.'.implode('|',$extensionfilter).'`', $file)) {
						$out[]=$folder.$file;
					}
				}

			}
			sort($out);
			return $out;

		}
	}

	public static function ensure_trailing_slash($folder)
	{
		$folder .= (substr($folder, -1) == '/' ? '' : '/');
		return $folder;
	}

	public static function is_dot_file($file) {
		return preg_match('`^\.`', $file);
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
	public function output($file,$asname='')
	{
	  if(empty($asname)) {
	    $asname=basename($file);
	  }
		switch(fileUtils::getFileExtension($file)){
			case 'jpeg':
			case 'jpg':
				$ctype='image/jpeg';
				break;
			case 'gif':
				$ctype='image/gif';
				break;

			case 'png':
				$ctype='image/png';
				break;
			default:
				$ctype='application/force-download';
				header('content-disposition:attachment;filename='.$asname);
				break;
		}
		header('Content-Type:'.$ctype);
		readfile($file);
		exit(0);
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
 /**
  * sanitizes a file name to prevent from evil behaviour
  * and removes any path to keep only the file name
  * @param $filename string file name to sanitize
  * @return string sanitized file name
  */
  public static function sanitize($filename)
  {
    $result = basename($filename);
    return escapeshellcmd($result);
  }

  public function getHumanFileSize($file)
  {
    $sizeArr = array('b','Kb','Mb','Gb','Tb');
    $size = filesize($file);
    $humansize = $size;
    $cycle=0;
    while($humansize>1) {
      $humansize/=1024;
      $cycle++;
    }
    return round($humansize*1024,1).' '.__($sizeArr[$cycle-1]);
  }
}
