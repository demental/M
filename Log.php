<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Log
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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
  
  public function getInstance($driver = 'none')
  {
    # code...
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
	 * Write message to log
	 *
	 * @access	public
	 * @static
	 */
	public static function message($message,$level = 'info')
	{
    self::getInstance(Config::get('driver'))->logMessage($message,$loglevel);
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