<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Built-in Office timeline module. Is used as a component by the "home" module
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Module_Timeline extends Module {
  public function doExecWidget()
  {
    $cat = $this->getParam('cat');
    $this->assign('cat',$cat);
  }
}
?>