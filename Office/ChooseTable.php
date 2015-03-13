<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Choosetable module creates a list of available modules for current user
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class M_Office_ChooseTable extends M_Office_Controller {
  function __construct() {
    parent::__construct();
    $o = array();
    $modules = $this->getOption('modulesToList');
    if($modules) {
      $o = $this->tree($modules);
    }
    $this->assign('choosetable',$o);
  }
  public function tree($modules)
  {
    $officeConfig = PEAR::getStaticProperty('m_office','options');
    $moduleconf = $officeConfig['modules'];
    $diff = array_diff(array_keys($_GET),array('module'));
    $o = array();
    foreach ($modules as $id => $module) {

      if(is_array($module)) {
        $res = array( 'title' => __("modules.$id.frontname"),
                      'icon'  => $moduleconf[$id]['icon']
                    );

        $res['submodules'] = $this->tree($module);
        if(in_array($_REQUEST['module'], $module)) {
          $res['expanded'] = true;
        }
      } else {
        $res = array( 'title' => __("modules.$module.frontname"),
                      'icon'  => $moduleconf[$module]['icon'],
                      'url'   => M_Office_Util::getQueryParams(array('module' => $module), $diff)

                    );
        if($_REQUEST['module'] == $module){
          $res['active'] = true;
        }
      }
      $o[] = $res;
    }
    return $o;
  }
}