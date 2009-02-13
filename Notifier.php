<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

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