<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Notifier
*/
/**
* M PHP Framework
*
* Notifier class which goal is to send and receive messages from other objects 
* @see observable pattern
*
* @package      M
* @subpackage   Notifier
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Notifier
{
	var $listeners=array();
	
  function & getInstance() { 
      static $instance; 
      if (!$instance) { 
          $instance = new Notifier; 
      } 
      return $instance;
  }
	function addListener(& $object) {
		if(!method_exists($object,'receiveMessage')){
			$this->broadCastMessage("L'objet de classe <b>".get_class($object)."</b> ne peut pas recevoir de messages.",NOTIFICATION_DEV_WARNING);
			return;
		}
		$this->listeners[]=&$object;
	}

	function broadCastMessage($message, $type = NOTIFICATION_NOTICE) {
		foreach($this->listeners as &$listener) {
			$listener->receiveMessage($message, $type);
		}
	}
}
?>