<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Config
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Global configuration helper
 * Store and retrieve configuration options accross application
 */

class Config {

	// @todo explain vars

	/**
	 * associative array that stores application preferences 
	 * Application preferences are configurable in the application's database
	 * This array is used as a basic caching system by Config::getPref()
	 * 
	 * @var $preftab array
	 * @access protected
	 */
	protected static $preftab = array();

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
	 *
	 * Get Alternate Languages (i.e. all languages that are handled by the application except the default language)
	 *
	 * @access	public
	 * @static
	 *
	 */
	public static function getAlternateLangs() {
		$l=Config::getAllLangs();
		$cur=array_search(DEFAULT_LANG,$l);
		unset($l[$cur]);
		return $l;
	}

	/**
	 *
	 * Get all language
	 *
	 * @access	public
	 * @return	array indexed array of all the languages iso2 codes handled by the application
	 * @static
	 *
	 */
	public static function getAllLangs() {
		return Config::get('installedLangs');
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
	public static function get($var) {
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
	public static function set($var,$val) {
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
	 * @return	string	Value
	 * @static
	 *
	 */
	public static function getPref($var) {
		if(!key_exists($var,self::$preftab)){
			$res = & DB_DataObject::factory('preferences');
			$res->var=$var;
			$res->find(true);
			if($res->type=='array') {
				$temp = explode("\n",$res->val);
				foreach($temp as $k=>$v) {
					$temp2 = explode(':',$v);
					$temp3[trim($temp2[0])] = trim($temp2[1]);
				}
				$res->val = $temp3;
			}
			self::$preftab[$var]=$res->val;
		}
		return self::$preftab[$var];
	}

	/**
	 *
	 * @todo what's this ?? (might be scheduled for deletion)
	 *
	 * @access	public
	 * @param	??		$item	??
	 * @param	??		$key	??
	 * @return	??		Value
	 * @static
	 *
	 */
	public static function arrayize(&$item,$key)
	{
	 # code...
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
		self::$preftab[$var]=$val;
		return self::$preftab[$var];
	}
}