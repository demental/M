<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Config
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Global configuration helper
 * Store and retrieve configuration options accross application
 */

class Config
{

	/**
	 * associative array that stores application preferences
	 * Application preferences are configurable in the application's database
	 * This array is used as a basic caching system by Config::getPref()
	 *
	 * @var $preftab array
	 * @access protected
	 */
	protected static $prefArr = array();


	/**
	 *
	 * associative array that stores application configuration values
	 * all the configuration values are stored in this array at runtime,
	 * using Config::load($arr) in M_Setup::setUpEnv()
	 *
	 * @var		array
	 * @access	protected
	 * @
	 */
	protected static $cfgArr = array();

  protected static $prefFile;
	/**
	 *
	 * Loads a configuration array
	 * if called several times, consecutive arrays are merged.
	 *
	 * @access	public
	 * @param	array	$array Associative array containing app's configuration values
	 * @static
	 *
	 */
	public static function load($array)
	{
		self::$cfgArr = array_merge(self::$cfgArr,$array);
	}

  /**
   * Returns (an creates if not set yet) the preference file name
   */
   protected static function getPrefFile()
   {
     if(!self::$prefFile) {
       $name = preg_replace('`\W`','',SITE_URL);
       self::$prefFile = 'preferences'.$name.'.php';
     }
     return self::$prefFile;
   }
	/**
	 *
	 * Loads a preference file into an array
	 *
	 * @access	public
	 * @param	string	$file filename with path
	 * @static
	 *
	 */
	public static function loadPrefFile()
	{
    $file = APP_ROOT . PROJECT_NAME . '/cache/' . self::getPrefFile();
    if (!file_exists($file))
    {
      // If preference file doesn't exist we will generate it
      self::savePrefFile();
    }
    self::$prefArr = unserialize(file_get_contents($file));
	}
	/**
	 *
	 * Generate a preference file from database configuration table
	 *
	 * @access	public
	 * @param	string	$file filename with path
	 * @static
	 *
	 */
	public static function savePrefFile()
	{
    $file = APP_ROOT . PROJECT_NAME . '/cache/' . self::getPrefFile();
    $setup = Mreg::get('setup');
    if(is_object($setup)) {
      $setup->setUpEnv();
    }
    $prefs = DB_DataObject::factory('preferences');
    $prefs->find();
    while($prefs->fetch())
    {
      self::$prefArr[$prefs->var] = self::parsePref($prefs);
    }
    file_put_contents($file, serialize(self::$prefArr));
	}

  public static function parsePref($pref)
  {
    if ($prefs->type == 'hidden') {
      self::$prefArr[$prefs->var] = unserialize($prefs->val);
    } elseif ($prefs->type == 'array') {
      $temp = explode("\n",$prefs->val);
      $temp3 = array();
      foreach($temp as $k=>$v)
      {
        $temp2 = explode(':',$v);
        if(empty($temp2[1])) {
          $temp3[] = trim($temp2[0]);
        } else {
          $temp3[trim($temp2[0])] = trim($temp2[1]);
        }
      }
      return $temp3;
    } else {
      return $prefs->val;
    }
  }

	/**
	 *
	 * Get Alternate Languages (i.e. all languages that are handled by the application except the default language)
	 *
	 * @access	public
	 * @static
	 *
	 */
	public static function getAlternateLangs()
	{
		$l=Config::getAllLangs();
		$cur=array_search(self::get('defaultLang'),$l);
		unset($l[$cur]);
		return $l;
	}

	/**
	 *
	 * Get all language/country pairs
	 *
	 * @access	public
	 * @return	array indexed array of all the languages/country iso2iso2 codes handled by the application
	 * @static
	 *
	 */
	public static function getAllLangs()
	{
		return Config::get('installedLangs');
	}

	/**
	 *
	 * Get all languages
	 *
	 * @access	public
	 * @return	array indexed array of all the languages iso2 codes handled by the application
	 * @static
	 *
	 */
	public static function getAllLocales()
	{
		$ret = Config::get('installedLocales');
		if(!is_array($ret)) {
      $ret = array();
		  foreach(self::getAllLangs() as $pair) {
		    $ret[] = substr($pair,0,2);
		  }
		  self::set('installedLocales',array_unique($ret));
		}
		return $ret;
	}

	/**
	 *
	 * Get config value
	 *
	 * @access	public
	 * @param	string	$var	Variable to get
	 * @return	string	Value
	 * @static
	 *
	 */
	public static function get($var)
	{
		return self::$cfgArr[$var];
	}

	/**
	 *
	 * Set config value
	 *
	 * @access	public
	 * @param	string	$var	Variable to set
	 * @param	string	$val	Value
	 * @static
	 *
	 */
	public static function set($var,$val)
	{
		self::$cfgArr[$var]=$val;
	}

	/**
	 *
	 * Get preferences value.
	 * Preferences are values stored in the application's database,
	 * in a table called 'preferences'
	 * allowing administrators to modify them through a web interface
	 *
	 * @access	public
	 * @param	string	$var	Variable to get
   * @param bool default true use cache file is true, database otherwise
	 * @return	string	Value
	 * @static
	 *
	 */


	public static function getPref($var, $use_cache = true)
	{
    if(!$use_cache) {
      return self::getPrefFromDatabase($var);
    }
		if(!key_exists($var,self::$prefArr))
		{
		  self::loadPrefFile();
		}
		return self::$prefArr[$var];
	}

  /**
   * Get preferences value from database storage
   * @access public
   * @param string $var Variable to get
   * @return mixed
   * @static
   */
  public static function getPrefFromDatabase($var) {
    $pref = DB_DataObject::factory('preferences');
    $pref->var = $var;
    $pref->find(true);
    return self::parsePref($pref);
  }

	/**
	 *
	 * Set preferences values - affects database
	 *
	 * @access	public
	 * @param	string	$var	Variable to set
	 * @param	string	$val	Value
	 * @return	string Value or false if the target preference is not found in the SQL table
	 * @static
	 *
	 */
	public static function setPref($var,$val)
	{
		$res = & DB_DataObject::factory('preferences');
		$res->var=$var;
		if(!$res->find(true)) {
			return false;
		}
		$res->val = $val;
		$res->update();
		self::$prefArr[$var]=$val;
		self::savePrefFile();
		return self::$prefArr[$var];
	}
}