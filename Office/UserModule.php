<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* For use in future refactoring of Office app
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class Office_UserModule extends Module {
  public function doExecLogin()
  {
    ob_clean();
    $do = User::getInstance('office')->getDBDO();
    $do->getPlugin('user')->prepareForLogin($do,$do->userFields['register'],$do->userFields['reminder']);
    $fb = MyFB::create($do);
    $form = new HTML_QuickForm('loginform','POST',URL::get('user/login'));
    $fb->useForm($form);
    $fb->getForm();
    $this->assignRef('form',$form);
    if($form->validate()) {
      $this->redirect(User::getInstance('office')->getTarget());
    }
  }
  public function doExecLogout()
  {
    User::getInstance('office')->logout();
    $this->redirect('');
  }
}