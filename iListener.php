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
 * Simple listener interface
 *
 **/
interface iListener {
  /**
   * @return array indexed array of events that can be handled by this listener
   */
  public function getEvents();
  /**
   * handles an event
   * @param $sender object that fired the event
   * @param $event string name of the event
   * @param $params additional params (optional)
   * @return string "fail", "bypass" or any other value
   */
	public function handleEvent($sender,$event,&$params = null);
}