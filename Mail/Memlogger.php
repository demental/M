<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Memlogger.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Mail driver that logs mails in memory
 */

class MailMemlogger extends Maman implements iMailDriver {
	public static $messages = array();
	public function sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false) {
		$mess = "Message from : ".$from[1]."<".$from[0].">\r\n".
						"to : ".$to."\r\n".
						"Subject : ".stripslashes($subject)."\r\n".
						"Message : ".stripslashes($note.$body)."\r\n".
						"\r\n";
		if(!is_array($attachments)){
			$attachments=array($attachments);
		}
		foreach($attachments as $k=>$v){
			$mess .= "Attachment : ".basename($v)."\r\n";
		}
		self::$messages []= $mess;
  }
}
