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
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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
    $dash = DB_DataObject::factory('blackboard');
    if(PEAR::isError($dash)) {
      M_Office_Util::refresh(M_Office_Util::getQueryParams(array('action'=>'install'),array_keys($_REQUEST)));
    }
    $dash->orderBy('date DESC');
    $dash->find();
    if(PEAR::isError($dash)) {
      M_Office_Util::refresh(M_Office_Util::getQueryParams(array('action'=>'install'),array_keys($_REQUEST)));
    }
    $this->assign('messages',$dash);
  }
  public function doExecAddmessage()
  {
    $dash = DB_DataObject::factory('blackboard');
    try{
      if(User::getInstance('office')->isLoggedIn()) {
        $dash->by = User::getInstance('office')->getId();
      }
    } catch(Exception $e) {
      
    }

    $form = new HTML_QuickForm('addmessageform','post',M_Office_Util::getQueryParams(array('action'=>'addmessage'),array_keys($_REQUEST)));
    $fb = MyFB::create($dash);
    $fb->useForm($form);
    $fb->getForm();
    if($form->validate()) {
      $form->process(array($fb,'processForm'),false);
      $this->setTemplate('home/messageline');
      $this->assign('mess',$dash);
    } else {
      $this->assign('form',$form);
    }
  }
  public function doExecInstall()
  {
    $q = "CREATE TABLE `blackboard` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `date` DATETIME NOT NULL ,
    `by` VARCHAR( 36 ) NULL ,
    `content` MEDIUMTEXT NOT NULL
    ) ENGINE = MYISAM ;";
    $op = PEAR::getStaticProperty('m_office', 'options');
    $mods = $op['modules'];
    MDB2::singleton(DB_URI)->query($q);
    $dash = DB_DataObject::factory('blackboard');
    if(PEAR::isError($dash)) {
      M_Office_Util::refresh(M_Office_Util::getQueryParams(array('regenerate'=>1),array_keys($_REQUEST)));
    }
  }
}
?>