<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Logger.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Mail driver that logs mails
 */

class Mail_logger extends Maman implements iMailDriver {
	public function sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false) {
  	/**
  	 * Record to logfile
  	 */
  		$newmailFile=date("Y-m-d-H-i-s").".mlog";
  		$fp=fopen($this->getConfig('log_folder').$newmailFile,"a+");
  		fwrite($fp,"Message from : ".$from[1]."<".$from[0].">\r\n");
  		fwrite($fp,"to : ".$to."\r\n");
  		fwrite($fp,"Subject : ".stripslashes($subject)."\r\n");
  		fwrite($fp,"Message : ".stripslashes($note.$body)."\r\n");
  		fwrite($fp,"\r\n");
  		if(!is_array($attachments)){
  			$attachments=array($attachments);
  		}
  		foreach($attachments as $k=>$v){
  			fwrite($fp,"Attachment : ".basename($v)."\r\n");
  		}
  		if($this->getConfig('sendmail')){
  			fwrite($fp,"This message was sent by mail.\r\n----------------------------------------\r\n\r\n");
  		}
  		fclose($fp);    
  }
}