<?php
// ==========================================================================================================
// = Notifier class which goal is to send and receive messages from other objects (@see observable pattern) =
// ==========================================================================================================
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