<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Mail
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * 
 * Mail class. Holds an Mtpl object so mails can be handled just like Module templates
 * Sent mails can be stored as log files (or not), mailed (or not), and also mailed to only one static recipient (for testing purpose)
 * depending on the configuration options setup in the PEAR::getStaticProperty('Mail','global')
 *
 *
 */
class Mail extends Maman {

	/**
	 *
	 * Mail Template
	 *
	 * @var		string
	 * @access	public
	 */
	public $template;

	/**
	 *
	 * Attachments
	 *
	 * @var		array
	 * @access	public
	 */
	public $_attachments;

	/**
	 *
	 * View
	 *
	 * @var		class
	 * @access	public
	 */
	public $view;

	/**
	 *
	 * From
	 *
	 * @var		array
	 * @access	public
	 */
	public $from;

	/**
	 *
	 * To
	 *
	 * @var		array
	 * @access	public
	 */
	public $to;

	/**
	 *
	 * Constructor
	 *
	 * @param $config	string	Template config
	 */
	function __construct($config) {
		$this->setConfig($config);
		$this->view = new Mtpl($this->getConfig('template_dir'));

	}

	/**
	 *
	 * Attach files
	 *
	 * @access	public
	 * @param	$filepath	string	Path to file
	 */
	public function addFile($filepath)
	{
		$this->_attachments[]=$filepath;
	}

	/**
	 *
	 * Set mail vars
	 *
	 * @param $vars
	 */
	function setVars($vars) {
		$this->fetched = false;
		$this->view->assignArray($vars);
		if(key_exists('reply-to',$vars)) {
			$this->replyTo = $vars['reply-to'];
		}
	}

	/**
	 *
	 * Assign mail vars
	 *
	 * @access	public
	 * @param	$var	string	Var
	 * @param	$val	string	Value
	 */
	public function assign($var,$val)
	{
		$this->fetched = false;
		$this->view->assign($var,$val);
	}

	/**
	 *
	 * Append mail vars
	 *
	 * @access	public
	 * @param	$var	string	Var
	 * @param	$val	string	Value
	 */
	public function append($var,$val)
	{
		$this->fetched = false;
		$this->view->append($var,$val);
	}

	/**
	 *
	 * Set mail template
	 *
	 * @param $tpl		string	Template
	 */
	function setTemplate($tpl) {
		$this->template = $tpl;
	}

	/**
	 *
	 * Email creation
	 *
	 * @access	public
	 * @static
	 * @param	$template	string	Template
	 * @return	Email		class
	 */
	public static function &factory ( $template )
	{
		$opt = array('all'=>PEAR::getStaticProperty('Mail','global'));

		$mail = new Mail($opt);
		$mail->setConfigValue('template',$template);
		return $mail;
	}

	/**
	 *
	 * Mail send to admin
	 *
	 * @access public
	 */
	public function sendToAdmin ()
	{
		return $this->sendTo($this->getConfig('from'),$this->getConfig('fromname'));
	}

	/**
	 *
	 * Get mail subject
	 *
	 * @access	public
	 * @return	Subject		string
	 */
	public function getSubject()
	{
		if($this->fetched) {
			return $this->subject;
		}
		$this->fetch();
		return $this->subject;
	}

	/**
	 *
	 * Get Alternate body
	 *
	 * @access	public
	 * @return	Body	string
	 */
	public function getAltbody()
	{
		if($this->fetched) {
			return $this->altbody;
		}
		$this->fetch();
		return $this->altbody;
	}

	/**
	 *
	 * Get body
	 *
	 * @access	public
	 * @return	Body	string
	 */
	public function getBody()
	{
		if($this->fetched) {
			return $this->body;
		}
		$this->fetch();
		return $this->body;
	}

	/**
	 *
	 * Fetch template
	 *
	 * @access public
	 */
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

  /**
   * Sets the sender email
   * @param array (sender email address,sender email address label)
   */
  public function setFromEmail($from)
  {
    $this->setConfigValue('from',$from[0]);
    $this->setConfigValue('fromname',$from[1]);    
  }
	/**
	 *
	 * Send mail to $mail
	 *
	 * @access	public
	 * @param	$mail string
	 */
	public function sendTo ($mail)
	{
		if(empty($this->from)) {
			$from = $this->from = array($this->getConfig('from'),$this->getConfig('fromname'));
		}
		$from = $this->from;
		$to=$this->getConfig('sendonlyto')?$this->getConfig('sendonlyto'):$mail;
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

	/**
	 *
	 * Raw Send Mail
	 *
	 * @access public
	 * @param $from			string	From
	 * @param $to			string	To
	 * @param $subject		string	Subject
	 * @param $htmlbody		string	HTML Body
	 * @param $altbody		string	Alternate body
	 * @param $options		array	Options
	 * @param $attachments	array	Attachments
	 * @param $html			boolean	Is HTML
	 */
	public function rawsend($from,$to,$subject,$htmlbody,$altbody=null,$options = null,$attachments=null,$html=false)
	{
		$this->_sendmail($from,$to,$subject,$htmlbody,$altbody,$options,$attachments,$html);
	}

	/**
	 *
	 * Get mail contents
	 *
	 * @access 	public
	 * @return 	Content
	 */
	public function getContents()
	{
		$out = new StdClass();
		$out->body=$this->view->fetch($this->getConfig('template'));
		$out->subject = $this->view->getCapture('subject');
		$out->altbody = $this->view->getCapture('altbody');


		return $out;
	}

	/**
	 *
	 * Send Mail
	 *
	 * @access private
	 * @param $from			string or array	From (if array, format is Array('from','fromname'))
	 * @param $to			string	To
	 * @param $subject		string	Subject
	 * @param $body			string	HTML Body
	 * @param $altbody		string	Alternate body
	 * @param $options		array	Options
	 * @param $attachments	array	Attachments
	 * @param $html			boolean	Is HTML
	 */
	private function _sendmail($from,$to,$subject,$body,$altbody = null, $options = null, $attachments = null, $html = false) {

		if(!is_array($from)){
			$from=array($from,$from);
		}
		if($only = $this->getConfig('sendonlyto')){
			$note="-------Note : Test mode enabled, this message should have been sent to ".$to."---------\r\n";
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
		/**
		 * Record to logfile
		 */
		if($this->getConfig('logmail')){
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
}