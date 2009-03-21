<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Mail
*/
/**
* M PHP Framework
*
* Mail class. Holds an Mtpl object so mails can be handled just like Module templates
* Sent mails can be stored as log files (or not), mailed (or not), and also mailed to only one static recipient (for testing purpose) 
* depending on the configuration options setup in the PEAR::getStaticProperty('Mail','global')
*
* @package      M
* @subpackage   Mail
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Mail extends Maman {
    public $template;
    public $_attachments;
    public $view;
    public $from;
    public $to;
    function __construct($config) {
        $this->setConfig($config);
        $this->view = new Mtpl($this->getConfig('template_dir'));

    }
    public function addFile($filepath)
    {
      $this->_attachments[]=$filepath;
    }
    function setVars($vars) {
      $this->fetched = false;
      $this->view->assignArray($vars);
      if(key_exists('reply-to',$vars)) {
        $this->replyTo = $vars['reply-to'];
      }
    }
    public function assign($var,$val)
    {
      $this->fetched = false;
      $this->view->assign($var,$val);
    }
    public function append($var,$val)
    {
      $this->fetched = false;      
      $this->view->append($var,$val);
    }    
    function setTemplate($tpl) {
        $this->template = $tpl;
    }
    public static function &factory ( $template )
    {
        $opt = array('all'=>PEAR::getStaticProperty('Mail','global'));

        $mail = new Mail($opt);
        $mail->setConfigValue('template',$template);
        return $mail;
    }
    public function sendToAdmin ()
    {
        return $this->sendTo($this->getConfig('from'),$this->getConfig('fromname'));
    }
    public function getSubject()
    {
      if($this->fetched) {
        return $this->subject;
      }
      $this->fetch();
      return $this->subject;
    }
    public function getAltbody()
    {
      if($this->fetched) {
        return $this->altbody;
      }
      $this->fetch();
      return $this->altbody;
    }    
    public function getBody()
    {
      if($this->fetched) {
        return $this->body;
      }
      $this->fetch();
      return $this->body;
    }
    public function fetch()
    {
      if($this->fetched) {return;}
      $this->body = trim($this->view->fetch($this->getConfig('template')));
      $this->altbody = $this->view->getCapture('altbody');
      if(empty($this->altbody)) {
        $this->altbody = $this->view->getCapture('alternate');        
      }
      $this->subject = trim($this->view->getCapture('subject'));
      $this->fetched = 1;
    }
    public function sendTo ($mail)
    {
      if(empty($this->from)) {
        $from = $this->from = array($this->getConfig('from'),$this->getConfig('fromname'));
      }
      $from = $this->from;
    	$to=$mail;
      $this->fetch();
        if($this->altbody) {
            $html = true;
        } else {
            $html = false;            
        }
        // TODO options, attachments
        $options = array();
        if($this->replyTo) {
          $options['reply-to'] = $this->replyTo;
        }
        $attachments = null;
    	if(is_array($to)){
    		foreach ($to as $e){
    			$this->_sendmail($from,$e,$this->subject,$this->body,$this->altbody,$options,$this->_attachments,$html);
    		}
    	} else {
    			$this->_sendmail($from,$to,$this->subject,$this->body,$this->altbody,$options,$this->_attachments,$html);
    	}
    }
    public function rawsend($from,$to,$subject,$htmlbody,$altbody=null,$options = null,$attachments=null,$html=false)
    {
      $this->_sendmail($from,$to,$subject,$htmlbody,$altbody,$options,$attachments,$html);
    }
    public function getContents()
    {
      $out = new StdClass();
    	$out->body=$this->view->fetch($this->getConfig('template'));
      $out->subject = $this->view->getCapture('subject');
      $out->altbody = $this->view->getCapture('altbody');

      
      return $out;
    }
    private function _sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false) {

        if(!is_array($from)){
    		$from=array($from,$from);
    	}
    	if($only = $this->getConfig('sendonlyto')){
    		$note="-------Note : Mode test activé, ce message était normalement destiné à ".$to."---------\r\n";
    		$to=$only;
    	}
    	if($this->getConfig('sendmail')){
    		$mail = new phpmailer();
        $mail->PluginDir='M/lib/phpmailer/';
    		if($this->getConfig('smtp')) {
    		  $mail->isSMTP();
          $mail->Host=$this->getConfig('smtphost');
    		}
    		$mail->CharSet=$this->getConfig('encoding');
    		$mail->AddAddress($to);                // Adresse email de reception 
    		$mail->Subject = $subject;                    // Sujet
    		$mail->Body = $note.$body;          // Corps du message
        $mail->AltBody=$altbody;
    		if(!is_array($from)){
    			$from=array($from,$from);
    		}
    		$mail->From = $from[0];              // Adresse email de l'expediteur (optionnel)
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
    				$attachments=array($attachments);
    			}
    			foreach($attachments as $k=>$v){
    				if(!$mail->AddAttachment($v, basename($v))){
    					echo "Erreur impossible d'ajouter pièce jointe $v";
    				}	
    			}		
    		}
    		$mail->IsHTML($html);
    	    $result = 	$mail->send();
    	} 
    	/**
    	 * Enregistrement en logfile
    	 */
    	if($this->getConfig('logmail')){
    		$newmailFile=date("Y-m-d-H-i-s").".mlog";
    		$fp=fopen($this->getConfig('log_folder').$newmailFile,"a+");
    		fwrite($fp,"Message de : ".$from[1]."<".$from[0].">\r\n");
    		fwrite($fp,"à : ".$to."\r\n");
    		fwrite($fp,"Sujet : ".stripslashes($subject)."\r\n");
    		fwrite($fp,"Message : ".stripslashes($note.$body)."\r\n");
    		fwrite($fp,"\r\n");
    		if(!is_array($attachments)){
    			$attachments=array($attachments);
    		}
    		foreach($attachments as $k=>$v){
    				fwrite($fp,"Pièce jointe : ".basename($v)."\r\n");
    		}
    		if($this->getConfig('sendmail')){
    			fwrite($fp,"Ce message a été envoyé par mail.\r\n----------------------------------------\r\n\r\n");
    	}
    		fclose($fp);
    	}
    }
}