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
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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
    return array('pregenerateform','postgenerateform','postgeneratelogin','preprocessform','prepareforlogin');
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
              $form->addRule($fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix,__('Veuillez entrer un mot de passe'),'required');
              $form->addRule($fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,__('Veuillez retaper votre mot de passe'),'required');
            }
            $form->insertElementBefore($pwd2,$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix);
            $form->addRule(array($fb->elementNamePrefix.$defs['pwd'].'2'.$fb->elementNamePostfix,$fb->elementNamePrefix.$defs['pwd'].$fb->elementNamePostfix),__('Vos deux mots de passe ne correspondent pas'),'compare');
            $form->addFormRule(array($this,'checkUniqueLogin'));

        }
        $this->_obj = $fb->_do;
	}
	public function checkUniqueLogin($values) {
	    $defs = $this->_obj->_getPluginsDef();
        $defs = $defs['user'];
        $test = DB_DataObject::factory($this->_obj->tableName());
        $test->whereAdd("id!='".$this->_obj->id."'");
        $test->{$defs['login']} = $values[$defs['login']];
        if($test->find(true)) {
          return array($defs['login']=>__('This username already exists, please choose another one'));
        }
        return true;
	}
	public function preProcessForm(&$values,&$fb,&$obj) {
      	$defs = $this->_obj->_getPluginsDef();
        $defs = $defs['user'];
        if(!is_array($fb->userEditableFields) || count($fb->userEditableFields==0)) {
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
	    }
	}
  function prepareForLogin($reminder=true,$register=true,&$obj)
  {
    $defs = $obj->_getPluginsDef();
    $defs = $defs['user'];    
      $this->_addregister = $register;
      $this->_addreminder = $reminder;
      
      if($register) {
          $obj->fb_formHeaderText = __('Déjà inscrit ?');
      } else {
          $obj->fb_formHeaderText = __('Identification');            
      }
      $obj->fb_submitText = '>> '.__('Valider');
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
      $obj->fb_formHeaderText = __('Mot de passe perdu ?');
      $obj->fb_submitText = '>> '.__('Envoyer');
      $obj->fb_fieldsToRender = array($defs['email']);
      $obj->fb_postGenerateFormCallback=array($this,'postGenerateReminder');
  }
  function postGenerateLogin(&$form,&$fb,$obj = null) {
      if($this->_addreminder) {
          $elt = HTML_QuickForm::createElement('static','remindertext','','<a href="'.$this->reminderUrl.'">'.__('Mot de passe perdu ?').'</a>');
          $form->insertElementBefore($elt,'__submit__');        
      }
      if($this->_addregister) {
          $form->addElement('header','newUser',__('Pas encore inscrit ?'));
          if($this->registerUrl) {
              $form->addElement('static','newUserLink','','<a href="'.$this->registerUrl.'">'.__('Inscrivez-vous').'</a>');
          }
      }
      $this->_obj = $fb->_do;
      $form->addFormRule(array($this,'validateLogin'));
  }
  function validateLogin($values) {
    $defs = $this->_obj->_getPluginsDef();
    $defs = $defs['user'];
    $noaccountError = $defs['noaccountError']?$defs['noaccountError']:__('Ce compte n\'existe pas');
    $passwordError = $defs['passwordError']?$defs['passwordError']:__('Mot de passe incorrect');      
    $inactiveError = $defs['inactiveError']?$defs['inactiveError']:__('Ce compte n\'est pas actif');      

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
      $elt = HTML_QuickForm::createElement('static','remindertext','',__('To retreive your password, enter your email address in the field below, then you will be sent your new password.'));
      $form->insertElementBefore($elt,$fb->elementNamePrefix.$defs['email'].$fb->elementNamePostfix);
  }

}