<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Log
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Very basic log class
 *
 */
class Log 
{
  protected static $instances;
  
  public function getInstance($driver = 'nolog')
  {
    if(!self::$instances[$driver]) {
      $file = 'M/Log/'.strtolower($driver).'.php';
      include $file;
      $class = 'Log_'.$driver;
      self::$instances[$driver] = new $class;
    }
    return self::$instances[$driver];
  }

	/**
	 *
	 * Info
	 *
	 * @access	public
	 * @static
	 */
	public static function info($message)
	{
		self::message($message);
	}
	/**
	 *
	 * Warn
	 *
	 * @access	public
	 * @static
	 */
	public static function warn($message)
	{
		self::message($message,'warn');
	}
	/**
	 *
	 * Error
	 *
	 * @access	public
	 * @static
	 */
	public static function error($message)
	{
		self::message($message,'error');
	}

	/**
	 *
	 * Write message to log
	 *
	 * @access	public
	 * @static
	 */
	public static function message($message,$level = 'info')
	{
    if(defined('LOG_DRIVER')) {
	    $driver = LOG_DRIVER;
    }
	  if(!$driver) $driver = 'nolog';
    self::getInstance($driver)->message($message,$level);
	}

	/**
	 *
	 * Mail message
	 *
	 * @access	public
	 * @static
	 */
	public static function mail($message) 
	{
		$m = Mail::factory('vide');
		$m->setVars(array('subject'=>'Log report from '.SITE_URL,'body'=>$message.'<h3>Request :</h3><pre>'.print_r($_REQUEST,true).'</pre><h3>Session</h3><pre>'.print_r($_SESSION,true)));
		$m->sendTo(TECH_EMAIL);
	}
}