<?php
/**
* M PHP Framework
*
* @package			M
* @subpackage		Component
* @author			Arnaud Sellenet <demental@sat2way.com>

* @license			http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version			0.1
*/

/**
* Component class, allows a component to be inserted into a template, including controller logic.
*/

class Component extends Dispatcher 
{
  /**
   * Constructor
   * 
   * @param $module	Module to display
   * @param $action	action to execute
   * @param $params	Modules parameters
   * @return string
   */
  function __construct($module, $action='index',$params = null) 
  {
      parent::__construct($module,$action,$params);
  }

  public function display ()
  {
      $this->page->hasLayout(false);

      return parent::display();
  }
  public function moduleInstance()
  {

    $args = func_get_args();
    $res = call_user_func_array(array('parent','moduleInstance'),$args);
    $res->isComponent(true);
    return $res;
  }
}