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
class Log {

	/**
	 *
	 * Log level
	 *
	 * @var		string
	 */
	const LOGLEVEL_INFO = 'Info';

	/**
	 *
	 * Log level warning
	 *
	 * @var		string
	 */
	const LOGLEVEL_WARNING = 'Warning';

	/**
	 *
	 * Log level notice
	 *
	 * @var		string
	 */
	const LOGLEVEL_NOTICE = 'Notice';


	/**
	 *
	 * Info
	 *
	 * @access	public
	 * @static
	 */
	public static function info ($message)
	{
		self::message($message);
	}

	/**
	 *
	 * Write message to log file
	 *
	 * @access	public
	 * @static
	 */
	public static function message($message,$level = Log::LOGLEVEL_INFO) {

		return;
		$file = Config::get('logfile');
		file_put_contents($file,date('Y-m-d H:i:s').' == '.$level.' == '.$message."\n",FILE_APPEND);

	}

	/**
	 *
	 * Mail message
	 *
	 * @access	public
	 * @static
	 */
	public static function mail($message) {
		$m = Mail::factory('vide');
		$m->setVars(array('subject'=>'Rapport log sur '.SITE_URL,'body'=>$message.'<h3>Requête :</h3><pre>'.print_r($_REQUEST,true).'</pre><h3>Session</h3><pre>'.print_r($_SESSION,true)));
		$m->sendTo(TECH_EMAIL);
	}
}
?>
