<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* M_Office homepage handling. Basically this class creates a "home" Module (built-in or the specific one created in the project structure) 
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_FrontEndHome extends M_Office_Controller {
    public function __construct() {
        parent::__construct();
        $tpl = new Mtpl(array('M/Office/Templates/',APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR));
        $this->assign('output',$tpl->fetch('home'));
        $this->assign('__action','dyn');
	  }    
}