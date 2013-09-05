<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Mandrill.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

require_once 'M/lib/Mandrill.php';

/**
 * Mail driver that uses phpmailer.
 */

class Mail_mandrill extends Maman implements iMailDriver {
	public function sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false) {
		if(!is_array($from)){
			$from=array($from,$from);
		}
    /*
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
    */

    if(!is_array($to)) {
      $to = array('email' => $to, 'name' => $to );
    }

		if(key_exists('reply-to',$options)){
      $headers = array('Reply-To' => $options['reply-to']);
    }

		if(null!=$attachments){
			if(!is_array($attachments)){
				$attachments = array($attachments);
			}
		  foreach($attachments as $k=>$v){
        $arr = array('type' => "application/octet-stream", 'name' => basename($v), 'content' => base64_encode(file_get_contents($v)));
        $attachments_request[]= $arr;
      }
    }

    $message = array(
      'html' => $body,
      'text' => $altbody,
      'subject' => $subject,
      'from_email' => $from[0],
      'from_name' => $from[1],
      'to' => array($to),
      'headers' => $headers,
      'attachments' => $attachments_request
    );

    $request =array(
      'type' => 'messages',
      'call' => 'send',
      'message' => $message
    );

//    $ret = Mandrill::call($request);
    $ret = Mandrill::__callStatic('call', array($request));

  }

}