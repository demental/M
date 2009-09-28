<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   T
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Translation class for static strings
 * be aware that this file also provides the __() as _e() functions !
 *
 */
class T {
	static $lang;
	static $instances=array();
	static $config = array('driver'=>'reader');
	protected $locale;
	protected $loaded=TRUE;
	protected $strings=array();
	public static function &getInstance( $lang=null )
	{
		if(is_null($lang)) {
			$lang = T::getLang();
		}
		if(empty($lang)) {
			$lang = DEFAULT_LANG;
		}
		if(key_exists($lang,T::$instances)) {
			return T::$instances[$lang];
		} else {
			$driver = T::getConfig('driver');
			if(empty($driver)) {
				$driver = 'reader';
			}
			$className = 'T_'.$driver;
			$classPath = 'M/T/'.strtolower($driver).'.php';
			require_once $classPath;
			$t = new $className;
			$t->init($lang);
			T::addInstance($t,$lang);
			return $t;
		}
	}
	public function getLocale()
	{
		return $this->locale;
	}
	public static function addInstance ( &$i, $l )
	{
		T::$instances[$l]=$i;
	}
	public function getStrings() {
		return $this->strings;
	}
	public static function setConfig ( $conf )
	{
		T::$config = $conf;
	}
	// TODO rename to getConfigValue
	public static function getConfig ( $value )
	{
		return T::$config[$value];
	}
	public function init ( $lang ,$verbose = false)
	{
		$this->locale=$lang;

		$file = T::getConfig('path').$lang.".xml";
		if($verbose) {
			echo 'Source file : '.$file."\n";
		}
		if($this->cacheIsUpToDate($lang,$file)) {
			$this->getStringsFromCache($lang,$verbose);
			if($verbose) {
				echo 'Cache is up to date, retreiving from cache'."\n";
			}
		} else {
			$lngtb = $this->getStringsFromXML($file,$verbose);
			$this->rebuildCache();
			if($verbose) {
				echo 'Cache was deprecated, retreiving from XML and rebuilding cache'."\n";
			}

		}
	}
	private function cacheIsUpToDate ($lang,$file)
	{
		$cachefile = T::getConfig('cacheDir').'/'.$lang.'.cache.php';
		if(!file_exists($cachefile)) {
			return false;
		}
		$timecache = filemtime($cachefile);
		$timexml = filemtime($file);
		if($timecache < $timexml) {
			return false;
		}
		return true;
	}
	private function rebuildCache ()
	{
		$cachefile = T::getConfig('cacheDir').'/'.$this->locale.'.cache.php';
		if(!@$fp=fopen($cachefile,'w+')) {
			return;
			// @see TODO in this getStringsFromXML()
			throw new Exception($cachefile.' is not writable. Please check permissions');
		}
		$data = '<?php $data = '.var_export($this->strings,true).';';
		fwrite($fp,$data);
		fclose($fp);
		Log::info('lang cache file rebuilt as '.$cachefile);
	}
	private function getStringsFromCache ($lang,$verbose = false)
	{
		$cachefile = T::getConfig('cacheDir').'/'.$lang.'.cache.php';
		require_once $cachefile;
		if($verbose) {
			echo 'Retrieving lang strings from cache file '.$cachefile."\n";
		}
		$this->setStrings($data);
	}
	private function getStringsFromXML ( $file, $verbose = false )
	{
		require_once 'XML/Unserializer.php';

		$xml=new XML_Unserializer();
		if(!file_exists($file)) {
			if($verbose) {
				echo 'file '.$file.' not found. Filling with an empty array'."\n";
			}
			$this->setStrings(array());
			return;
			// TODO this causes problem in the application building process
			// because whenever we launch a command that relies somewhat on __()
			// it complains about lang.xml file not found
			$this->loaded = false;
			throw new Exception ('Translate file '.$file.' not found !');
			return;
		}
		$xmlC=file_get_contents($file);
		$xml->setOption('encoding',T::getConfig('encoding'));
		$xml->unserialize($xmlC);
		$lngtb = $xml->getUnserializedData();
		if($verbose) {
			echo 'Retrieving lang strings from XML file '.$file.' encoding '.T::getConfig('encoding')."\n";
		}
		$lngtb=T::linearize($lngtb);
		$this->setStrings($lngtb);
	}

	public function save($verbose = false) {
		require_once 'XML/Serializer.php';

		$file = T::getConfig('path').$this->locale.".xml";
		if($verbose) {
			echo '
current language ('.$this->locale.') contains '.count($this->strings).' strings..
saving to '.$file.'...
          ';
		}
		$serializer = new XML_Serializer();
		// perform serialization

		$lngtb=T::unlinearize($this->strings);
		$serializer->setOption("addDecl",TRUE);
		$serializer->setOption("encoding",T::getConfig('encoding'));
		$result = $serializer->serialize($lngtb);

		// check result code and save XML if success

		if($result === true){
			$res = $serializer->getSerializedData();
			$nb = file_put_contents($file,$res);
			if($verbose) {
				if($nb) {
					echo '
saving DONE
            ';
				} else {
					echo '
              Could not save '.$file.'
                          ';

				}
			}
		} elseif($verbose) {
			echo '
Error while serializing data !
          ';
			return false;
		}
		if($nb) {
			return true;
		} else {
			return false;
		}
	}
	public function getString($key)
	{
		if(key_exists($key,$this->strings)) {
			return  $this->strings[$key];
		}
		return $key;
	}
	public static function getLang ()
	{
		return T::$lang;
	}

	public static function setLang ( $lang = null )
	{
		if(!is_null($lang)) {
			T::$lang = $lang;
			Log::info('Passage en langue '.T::$lang);
		}
		return T::$lang;
	}
	public function setStrings( $arr )
	{
		if(!is_array($arr)) {
			$this->strings = array();
		} else {
			$this->strings = $arr;
		}
	}
	public function __destruct()
	{

		if(T::getConfig('saveresult')) {
			$this->save();
		}
	}
	public static function unlinearize ( $array )
	{
		$out=array();
		foreach ($array as $k=>$v){
			$out[]=array('cle'=>$k,'valeur'=>$v);
		}
		return $out;
	}
	public static function linearize ( $array )
	{
		$out=array();
		if(!is_array($array) || count($array)==0){
			return array();
		}
		foreach ($array['XML_Serializer_Tag'] as $k=>$v){
			@$out[$v['cle']]=$v['valeur'];
		}
		return $out;
	}
}


/**
 * helper
 *
 **/
function __( $string, $args=null ) {
	//  try {
	$tr = T::getInstance();
	//  } catch (Exception $e) {
	//    die($e->getMessage());
	//  }
	return $tr->translate($string,$args);
}
function _e($string,$args = null) {
	echo __($string,$args);
}