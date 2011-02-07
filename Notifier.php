<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Notifier class which goal is to send and receive messages from other objects
 * @see observable pattern
 *
 */
class Notifier
{
	private $_listeners=array();

	function & getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new Notifier;
		}
		return $instance;
	}
	function addListener(iListener $object) {
		$this->_listeners[] = $object;
	}

	function broadCastMessage($sender,$message) {
		foreach($this->_listeners as $listener) {
			$listener->handleEvent($sender,'notification',$message);
		}
	}
}