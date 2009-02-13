<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

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
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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