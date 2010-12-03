<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   URL
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Url generation class. Includes "old" and "new" flavour
 * the new flavour uses PEAR Net_URL_Mapper and is more powerful. But not used in production yet...
 * "old" flavour simply creates a module/action then appends the other passed variables as a query string
 *
 */
class URL {

  /**
   * $method string "old" or "new"
   * @access public
   * @static
   * URL can generate urls in two flavours
   */

	public static $method='old';

	/**
	 * $bases array
	 * @access public
	 * @static
	 * stores current, secure and unsecure root urls for the running application.
	 * 
	 */
	public static $bases;
  
  /**
   * setBases initialize the static property $bases. this method MUST be called BEFORE any URL generation
   * tipically in the configuration process.
   * This allows to generate URLS for the application in a regular or ssl way (for example content pages will be called using the regular http scheme, user specific pages using the ssl protocol)
   * @param $bases array including one or three strings.
   * examples :
   * // If your application does not use SSL access
   * URL::setBases(array('http://www.mydomain.tld'));
   * 
   * // If your application uses only SSL access
   * URL::setBases(array('https://secure.mydomain.tld')); 
   * 
   * // If your application is running on both http and https domains, you may way the enduser to browse the non-critical pages using http protocol, 
   * // and its private interface using the ssl protocol.
   * URL::setBases(array('http://www.mydomain.tld','secure'=>'https://secure/mydomain.tld','unsecure'=>'http://www.mydomain.tld'));
   * 
   */
	public static function setBases($bases) {
		self::$bases = $bases;
	}
	/**
	 * returns previously initialized url bases
	 */
	public static function getBases()
	{
		return self::$bases;
	}
	/**
	 * generates and echoes an URL
	 * @see URL::get for parameters detail
	 */
	public static function e($route,$params = null,$lang=null,$isSecure=null)
	{
		echo self::get($route,$params,$lang,$isSecure);
	}
	
	/**
	 * generates and echoes a relative path.
	 * @see URL::getAnchor for parameters detail
	 */
	public static function ea($route,$params = null,$lang=null)
	{
		echo self::getAnchor($route,$params,$lang);
	}
	
	/**
	 * generates and returns an URL
	 * @param $route string module/action string
	 * @param $params array associative array of additional parameters to include in the generated URL
	 * @param $lang string specifies language for the generated url. If not provided uses the current language
	 * @param $isSecure bool wether use current (null), secure (true) or unsecure(false) base for the generated url
	 * @return string generated absolute URL
	 */	
	public static function get($route,$params = null,$lang=null,$isSecure=null)
	{
		if($isSecure===true) {
			$url = self::$bases['secure'];
		} elseif($isSecure===false) {
			$url = self::$bases['unsecure'];
		} else {
			$url = self::$bases[0];
		}
		return $url.self::getAnchor($route,$params,$lang);
	}
  /**
   * generates and returns a relative path.
	 * @param $route string module/action string
	 * @param $params array associative array of additional parameters to include in the generated URL
	 * @param $lang string specifies language for the generated url. If not provided uses the current language
	 * @return string generated relative path
	 **/

	public static function getAnchor($route,$params = null,$lang=null)
	{
		if(self::$method=='old') {
			return self::getOldAnchor($route,$params,$lang);
		}
		unset($params['lang']);
		unset($params['module']);
		unset($params['action']);

		if($lang==null) {
			$lang=T::getLang();
		}
		$params['lang'] = $lang;
		list($params['module'],$params['action']) = explode('/',$route);
		if(empty($params['action'])) {$params['action'] = null;}
		return Net_URL_Mapper::getInstance()->generate($params);
	}
  /**
   * generates and returns a relative path when URL is set to the "old" method.
   * @see URL::getAnchor() for parameters detail
	 * @return string generated relative path
   **/
	public static function getOldAnchor($route,$params = null,$lang=null)
	{

		unset($params['lang']);
		unset($params['module']);
		unset($params['action']);

		if($lang==null) {
			$lang=T::getLang();
		}
		if($params == null) {
			return $lang.'/'.$route;
		}
		return $lang.'/'.$route.'?'.http_build_query($params);
	}
	/**
	 * generates and returns the currently requested URL for a specific language
	 * @param $lang string language
	 * @return string generated url
	 */
	public static function getFor($lang)
	{
		$g = $_GET;
		$route = $g['module'].($g['action']?'/'.$g['action']:'');
		unset($g['module']);
		unset($g['action']);
		return self::get($route,$g,$lang);
	}
	/**
	 * generates and returns the currently requested URL
	 * @return string generated url
	 */

	public static function getself($arr = null)
	{
	  if(is_array($arr)) {
	    $arr = array_merge($_GET,$arr);
	  }
		return self::get($_GET['module'].'/'.$_GET['action'],$arr);
	}
	/**
	 * generates and echoes the currently requested URL
	 */	
	public static function eself()
	{
		echo URL::getself();
	}
  /**
   * @todo
   * generates and returns an url merging additional parameters to the currently requested one
   * this method will replace the one used in office applications ( M_Office_Util::getQueryParams )
   */
  public static function merge($add = array() , $remove = array(), $entities = false)
  {
    # code...
  }
}