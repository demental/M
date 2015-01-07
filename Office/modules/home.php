<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* default Office homepage Module called by M_Office_FrontendHome
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Module_home extends Module {
  public function getCacheId($action)
  {
    return false;
  }
  public function doExecIndex()
  {
  }
}
