<?php
/**
* M PHP Framework
*
* Abstract class for plugins
*
* @package      M
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

abstract class M_Plugin implements iListener
{
  /**
   * returns array of events that this plugin can handle
   */
  public function getEvents()
  {
    return array(
      ''
      );
  }
  /**
   * Fires the plugin method attached to an event, if this one can handle it
   * Returns a "status" flag which can be :
   * - 'fail' => the sender must abort the original event and return a failure
   * - 'bypass' => the sender must abort the original event but behaves as if it succeeded (@see db_dataobject_plugin_officepack::delete())
   * - any other value => the sender executes the event as it should
   * These statuses are mostly useful for 'pre...' events, as it alters the way the event is really executed.
   */
  public function handleEvent($sender,$event,&$params = null)
  {

    if(!in_array($event,$this->getEvents())) return;

    if(!is_array($params)) {
      $params = array();
    }
    $ret = call_user_func_array(array($this,$event),array_merge($params,array(&$sender)));

    return $ret;
  }
  /**
   * In case an event is fired and the expected behaviour for it is to return a value, 
   * the plugin must use this method to return the result
   * @param mixed whatever must be returned and retreived by the event caller.
   */
  public function returnStatus($returnedValue)
  {
    $c = new StdClass();
    $c->status = 'return';
    $c->return = $returnedValue;
    return $c;
  }
  
  public function getFolderName()
  {
    return strtolower(preg_replace('`DB_DataObject_Plugin_`','',get_class($this)));
  }
  
}