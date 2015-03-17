<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Authentication and user initialization handler
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_Auth extends M_Office_Controller {
  public function __construct() {
    parent::__construct();
    if(key_exists('logout',$_REQUEST)){
      User::getInstance('office')->logout();
      M_Office_Util::refresh();
    }
    if(!User::getInstance('office')->isLoggedIn()){
      $this->loginForm(User::getInstance('office')->containers['office']['table']);
    }
  }

  public function loginForm($table) {
    $form = new MyQuickForm('loginform', 'POST', M_Office_Util::getQueryParams(array(), array(), false), '_self', null, true);
    $authDO = DB_DataObject::factory($table);

    $authDO->prepareForLogin(false,false);
    $authFB = MyFB::create($authDO);
    $authFB->useForm($form);
    $authFB->getForm();
    if($form->validate()){
      M_Office_Util::refresh($_SERVER['REQUEST_URI']);
    }
    M_Office::$dsp='login';
    $this->assign('loginForm',$form);
  }
}
