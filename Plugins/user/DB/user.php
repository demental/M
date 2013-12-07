<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_User
*/
/**
* M PHP Framework
*
* User authentication plugin
* Works with M_User (M/User.php)
*
* @package      M
* @subpackage   DB_DataObject_Plugin_User
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


 if(!defined('TMP_PATH')){
 	define('TMP_PATH',ini_get('upload_tmp_dir'));
 }

class DB_DataObject_Plugin_User extends M_Plugin
{
  public $reminderUrl='user/login';
//    var $registerUrl='user/register';
  protected $_usercontext;
  public function getEvents()
  {
    return array('pregenerateform','postgenerateform','postgeneratelogin','preprocessform','prepareforlogin','encrypt','clearpwd','generatepassword');
  }
	public function preGenerateForm(&$fb,&$obj)
	{
	  $defs = $obj->_getPluginsDef();
	  $defs = $defs['user'];
    $obj->fb_preDefElements[$defs['pwd']] = HTML_QuickForm::createElement('password',$defs['pwd'],$obj->fb_fieldLabels[$defs['pwd']]);
	  $obj->fb_excludeFromAutoRules[]=$defs['pwd'];
	}
	public function postGenerateForm(&$form,&$fb,&$obj) {
	  $defs = $obj->_getPluginsDef();
	  $defs = $defs['user'];
        if($form->elementExists($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix)) {

            $pwd = $form->getElement($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix);
            $pwd->setLabel($obj->fb_fieldLabels[$defs['pwd']].__('(Vérification)'));
            $pwd->setValue('');
    	      $pwd2 = HTML_QuickForm::createElement('password',$fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,
              $obj->fb_fieldLabels[$defs['pwd']]);

            if(empty($obj->id)) {
              $form->addRule($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix,__('user.register.error.required_password'),'required');
              $form->addRule($fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,__('user.register.error.required_password'),'required');
            }
            $form->insertElementBefore($pwd2,$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix);
            $form->addRule(array($fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix),__('user.register.error.unmatched_password'),'compare');
            $form->addFormRule(array($this,'checkUniqueLogin'));

        }
        $form->setDefaults(array($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix=>'',$fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix=>''));
        $this->_obj = $fb->_do;
	}
	public function checkUniqueLogin($values) {
	      $defs = $this->_obj->_getPluginsDef();
        $defs = $defs['user'];
        $test = DB_DataObject::factory($this->_obj->tableName());
        $test->whereAdd("id!='".$this->_obj->id."'");
        $test->{$defs['login']} = $values[$defs['login']];
        if($test->find(true)) {
          return array($defs['login']=>__('user.register.error.already_exists'));
        }
        return true;
	}
	public function preProcessForm(&$values,&$fb,&$obj) {
      	$defs = $this->_obj->_getPluginsDef();
        $defs = $defs['user'];
        if(!is_array($fb->userEditableFields) || count($fb->userEditableFields)==0) {
              $fb->populateOptions();
        }
        if(count($fb->userEditableFields) ==0) {
          $fb->userEditableFields = $fb->fieldsToRender;
        }
	    if(empty($values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix])) {
	        if($index = array_search($defs['pwd'],$fb->userEditableFields)) {
	            unset($fb->userEditableFields[$index]);
	        }
	    } elseif($defs['passEncryption']) {
	        $values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix] = call_user_func($defs['passEncryption'],$values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix]);
	    } else {
	      $values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix] = $this->encrypt($values[$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix],$this->_obj)->return;
	    }
	}
  function prepareForLogin($reminder=true,$register=true,&$obj)
  {

    $defs = $obj->_getPluginsDef();
    $defs = $defs['user'];
      $this->_addregister = $register;
      $this->_addreminder = $reminder;

      if($register) {
          $obj->fb_formHeaderText = __('user.login.title.already_signedin');
      } else {
          $obj->fb_formHeaderText = __('user.login.title.main');
      }
      $obj->fb_submitText = '>> '.__('user.login.link.submit');
      $this->_usercontext = $defs['context'];
      $obj->fb_fieldsToRender = array($defs['login'],$defs['pwd']);
      $obj->fb_preDefOrder = array($defs['login'],$defs['pwd']);
      if(method_exists($obj,'postGenerateLogin')) {
        $obj->fb_postGenerateFormCallback=array($obj,'postGenerateLogin');
      } else {
        $obj->fb_postGenerateFormCallback=array($this,'postGenerateLogin');
      }
  }
  function prepareForReminder(&$obj)
  {
    $defs = $obj->_getPluginsDef();
    $defs = $defs['user'];
      $obj->fb_formHeaderText = __('user.reminder.title');
      $obj->fb_submitText = '>> '.__('user.reminder.submit');
      $obj->fb_fieldsToRender = array($defs['email']);
      $obj->fb_postGenerateFormCallback=array($this,'postGenerateReminder');
  }
  function postGenerateLogin(&$form,&$fb,$obj = null) {
      if($this->_addreminder) {
          $elt = HTML_QuickForm::createElement('static','remindertext','','<a href="'.$this->reminderUrl.'">'.__('user.login.link.reminder').'</a>');
          $form->insertElementBefore($elt,'__submit__');
      }
      if($this->_addregister) {
          $form->addElement('header','newUser',__('user.login.register.title'));
          if($this->registerUrl) {
              $form->addElement('static','newUserLink','','<a href="'.$this->registerUrl.'">'.__('user.login.link.register').'</a>');
          }
      }
      $this->_obj = $fb->_do;
      $form->addFormRule(array($this,'validateLogin'));
  }
  function validateLogin($values) {

    $defs = $this->_obj->_getPluginsDef();
    $defs = $defs['user'];
    $noaccountError = $defs['noaccountError']?$defs['noaccountError']:__('user.login.error.noaccount');
    $passwordError = $defs['passwordError']?$defs['passwordError']:__('user.login.error.wrongpassword');
    $inactiveError = $defs['inactiveError']?$defs['inactiveError']:__('user.login.error.inactiveaccount');

      if(User::getInstance($this->_usercontext)->login($values[$defs['login']],$values[$defs['pwd']])) {
          if(!empty($defs['valid']) && !User::getInstance($this->_usercontext)->getDBDO()->{$defs['valid']}) {
              User::getInstance($this->_usercontext)->logout();
              return array($defs['login']=>$inactiveError);
          }
          if(method_exists(User::getInstance($this->_usercontext)->getDBDO(),'validateLogin')) {
            $ret = User::getInstance($this->_usercontext)->getDBDO()->validateLogin($values);
            if($ret===true) {
              return true;
            } else {
              User::getInstance($this->_usercontext)->logout();
              return $ret;
            }
          } else {
            return true;
          }
      } else {
          switch(User::getInstance($this->_usercontext)->_error){
              case ERROR_NO_USER:
                  return array($defs['login']=>$noaccountError);
                  break;
              default:
                  return array($defs['pwd']=>$passwordError);
                  break;
          }
      }
  }
  function setContext($c) {
    $this->_usercontext=$c;
  }
  function postGenerateReminder(&$form,&$fb) {
      $do = $fb->_do;
      $defs = $do->_getPluginsDef();
      $defs = $defs['user'];
      $elt = HTML_QuickForm::createElement('static','remindertext','',__('user.reminder.text'));
      $form->insertElementBefore($elt,$fb->elementNamePrefix.$defs['email'].$fb->elementNamePostfix);
  }
  /**
   * Encrypts obj's password field
   * @param string
   */
  public function encrypt($pwd,$obj)
  {
    $def = $obj->_getPluginsDef();
    $encryption_method = $def['user']['passEncryption'];
    return $this->returnStatus(call_user_func_array($encryption_method, array($pwd)));
  }

  /**
   * Returns unencrypted password
   * @return string
   */
  function clearPwd($obj) {
    $defs = $obj->_getPluginsDef();
    $field = $defs['user']['pwd'];
    return $this->returnStatus(M_Crypt::decrypt($obj->{$field},ENCSALT));
  }

  /**
   * Generates a new random password
   * @return string clear password
   */
  public function generatePassword($obj) {
    $defs = $obj->_getPluginsDef();
    $field = $defs['user']['pwd'];
    require_once 'Text/Password.php';
    $pwd = Text_Password::create(8);
    $obj->{$field} = $this->encrypt($pwd,$obj)->return;
    return $this->returnStatus($pwd);
  }


}