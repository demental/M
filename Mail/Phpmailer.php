<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Phpmailer.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Mail driver that uses phpmailer.
 */

class Mail_phpmailer extends Maman implements iMailDriver {
	public function sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false) {
		if(!is_array($from)){
			$from=array($from,$from);
		}

		$mail = new phpmailer();
		$mail->PluginDir='M/lib/phpmailer/';

		if($this->getConfig('smtp')) {
			$mail->isSMTP();
			$mail->Host=$this->getConfig('smtphost');
      if($this->getConfig('smtpusername')) {
        $mail->SMTPAuth = true;
        $mail->Port = $this->getConfig('smtpport') ? $this->getConfig('smtpport') : 25;
        $mail->SMTPDebug = $this->smtpdebug;
        $mail->Username = $this->getConfig('smtpusername');
        $mail->Password = $this->getConfig('smtppassword');
      }
		}

		$mail->CharSet=$this->getConfig('encoding');
		$mail->AddAddress($to);
		$mail->Subject = $subject;
		$mail->Body = $note.$body;
		$mail->AltBody=$altbody;
		if(!is_array($from)){
			$from=array($from,$from);
		}
		$mail->From = $from[0];
		$mail->FromName = $from[1];

		if(key_exists('reply-to',$options)){
			$mail->AddReplyTo($options['reply-to']);
			unset($options['reply-to']);
		}
		if(key_exists('Sender',$options)) {
			$mail->Sender = $options['Sender'];
		}

		if(null!=$attachments){
			if(!is_array($attachments)){
				$attachments = array($attachments);
			}
			foreach($attachments as $k=>$v){
				if(!$mail->AddAttachment($v, basename($v))){
					trigger_error("Attachment $v could not be added");
				}
			}
		}
		$mail->IsHTML($html);
		$result = 	$mail->send();
  }

}