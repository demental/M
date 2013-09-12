<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   T
 * @author       Arnaud Sellenet <demental at github>
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
	public static $lang;
	public static $culture;
	public static $instances=array();
	public static $config = array('driver'=>'reader');
  protected static $paths;

	protected $locale;
	protected $loaded = TRUE;
	protected $strings = array();


	public static function &getInstance( $lang=null )
	{
		if(is_null($lang)) {
			$lang = T::getLang();
		}
		if(empty($lang)) {
			$lang = Config::get('defaultLang');
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
  /**
   * Returns countrycode
   */
	public function getCountry()
	{
	 return substr(self::getLang(),2,2);
	}
  /**
   * Returns language (first 2 chars of T::getLang())
   */
	public static function getLocale()
	{
	 return substr(self::getLang(),0,2);
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

  public static function paths() {
    if(!is_array(self::$paths)) {
      self::$paths = array(APP_ROOT.PROJECT_NAME.'/lang/', APP_ROOT.PROJECT_NAME.'/'.APP_NAME.'/lang/');
    }
    return self::$paths;
  }

  public static function addPath($path)
  {
    if(!is_dir($path)) return;
    self::paths();
    self::$paths[] = $path;
  }

	public function init( $lang )
	{
		$this->locale=substr($lang,0,2);

		if((!T::getConfig('autoexpire') && $this->cacheExists($this->locale)) || $this->cacheIsUpToDate($this->locale)) {
			$lngtb = $this->getStringsFromCache($this->locale);
      $this->setStrings($lngtb);
      $this->log('Cache is up to date, retreiving from cache');
		} else {
      $lngtb = array();
      foreach($this->files_to_load($lang) as $file) {
        $this->getStringsFromFile($file, $lngtb);
      }
      $this->setStrings($lngtb);
      $this->rebuildCache();
      $this->log('Cache was deprecated, retreiving from XML and rebuilding cache');
		}
	}

  public function files_to_load($lang)
  {
    foreach(T::paths() as $path) {
      foreach(FileUtils::getAllFiles($path) as $file) {
        if(substr($file, -6, 3) == $lang.'.') {
          $files_to_load[]= $file;
        }
      }
    }
    return $files_to_load;
  }

	private function cacheIsUpToDate ($lang)
	{
		$cachefile = $this->getCacheFile($lang);
		if(!file_exists($cachefile)) {
			return false;
		}

		$timecache = filemtime($cachefile);
    foreach($this->files_to_load($lang) as $file) {
      $time = filemtime($file);
  		if($timecache < $time) {
  			return false;
      }
    }
		return true;
	}

  public function getCacheFile($lang)
  {
    return APP_ROOT.PROJECT_NAME.'/'.APP_NAME.'/cache/'.$lang.'.cache.php';
  }
  private function cacheExists($lang)
  {
		if(!file_exists($this->getCacheFile($lang))) {
			return false;
		}
    return true;
  }
	private function rebuildCache ()
	{
		$cachefile = $this->getCacheFile($this->locale);
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

	private function getStringsFromCache ($lang)
	{
	  Log::info('T::retreiving strings from cache');
		$cachefile = $this->getCacheFile($lang);
		require_once $cachefile;
    $this->log('Retrieving lang strings from cache file '.$cachefile);
    return $data;
	}

  private function getStringsFromfile( $file, &$lngtb ) {

    $extension = FileUtils::getFileExtension($file);
    $method = 'getStringsFrom'.$extension;
    if(!method_exists($this, $method)) return;

    $reflectionMethod = new ReflectionMethod('T', $method);
    $reflectionMethod->invokeArgs($this, array($file, &$lngtb));
  }

  public function getStringsFromYML( $file, &$lngtb )
  {
    $yaml = Spyc::YAMLLoad($file);
    $result = MArray::flatten_keys($yaml[$this->locale]);

    if(is_array($result)) {
      $lngtb = array_merge($lngtb, $result);
    }

  }
  public function getStringsFromXML ( $file, &$lngtb )
	{
		require_once 'XML/Unserializer.php';
    $this->log('R etreiving strings from xml');

		$serializer = new XML_Unserializer();
		$xml_content = file_get_contents($file);
		$serializer->setOption('encoding',T::getConfig('encoding'));
    $serializer->setOption(XML_SERIALIZER_OPTION_ENTITIES, XML_SERIALIZER_ENTITIES_NONE);
		$serializer->unserialize($xml_content);

    $this->log('Retrieving lang strings from XML file '.$file.' encoding '.T::getConfig('encoding'));

		$local_lngtb = T::linearize($serializer->getUnserializedData());

		$lngtb = array_merge($lngtb, $local_lngtb);
	}

	public function save($destfile= '') {
		require_once 'XML/Serializer.php';

    if(!empty($destfile)) {
      $file = $destfile;
    } else {
      $file = T::getConfig('path').$this->locale.".xml";
    }
    $this->log('current language ('.$this->locale.') contains '.count($this->strings).' strings.. saving to '.$file.'...');
		$serializer = new XML_Serializer();
    $serializer->setOption(XML_SERIALIZER_OPTION_ENTITIES, XML_SERIALIZER_ENTITIES_NONE);
		// perform serialization

		$lngtb = T::unlinearize($this->strings);
		$serializer->setOption("addDecl",TRUE);
		$serializer->setOption("encoding",T::getConfig('encoding'));
		$result = $serializer->serialize($lngtb);

		// check result code and save XML if success

		if($result === true){
			$res = $serializer->getSerializedData();
			$nb = file_put_contents($file,$res);
			if($nb) {
				$this->log('saving DONE');
			} else {
				$this->log('Could not save '.$file);
			}

		} else {
			$this->log('Error while serializing data !');
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
  public static function getCulture()
  {
    if(!T::$culture) {
      if(strlen(T::$lang)==4) {
        T::$culture = substr(T::$lang,0,2).'_'.strtoupper(substr(T::$lang,2,2));
      } else {
        if(T::$lang=='fr') {
          return 'fr_FR';
        } elseif(T::$lang=='en') {
          return 'en_GB';
        }
      }
    }
    return T::$culture;
  }
	public static function setLang ( $lang = null )
	{
		if(!is_null($lang)) {
			T::$lang = $lang;
      T::$culture = null;
		}
    Log::info('T::setLang - Switching to '.$lang);
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

  public function log($message)
  {
    # code...
  }
}


/**
 * helper
 * don't init it if already declared by other tools.
 **/
if(!function_exists('__')) {
  function __( $string, $args=null ) {
    $tr = T::getInstance();
  	return $tr->translate($string,$args);
  }
}
if(!function_exists('_e')) {
  function _e($string,$args = null) {
    echo __($string,$args);
  }
}

if(!function_exists('_d')) {
  function _d($date) {
    return date(__('date.format_day'), strtotime($date));
  }
}
if(!function_exists('_ed')) {
  function _ed($date) {
    echo _d($date);
  }
}

if(!function_exists('_t')) {
  function _t($time) {
    return date(__('date.format_time'), strtotime($time));
  }
}
if(!function_exists('_et')) {
  function _et($date) {
    echo _t($date);
  }
}