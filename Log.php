<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Log
 * @author       Arnaud Sellenet <demental at github>
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

  public static function getInstance($driver = null)
  {
    if(is_null($driver)) {
      if(defined('LOG_DRIVER')) {
        $driver = LOG_DRIVER;
      } else {
        $driver = 'nolog';
      }
    }
    if(!self::$instances[$driver]) {
      $class = 'Log_'.$driver;
      self::$instances[$driver] = new $class;
      if(method_exists(self::$instances[$driver],'init')) {
        self::$instances[$driver]->init(array());
      }
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
    self::getInstance()->message($message,$level);
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
