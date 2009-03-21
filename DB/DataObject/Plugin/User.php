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

require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_User extends DB_DataObject_Plugin
{
    public $plugin_name='user';
    public $reminderUrl='user/login';
//    var $registerUrl='user/register';
    protected $_usercontext;
	function preGenerateForm(&$fb,&$obj)
	{
      $obj->fb_preDefElements[$obj->userFields['pwd']] = HTML_QuickForm::createElement('password',$obj->userFields['pwd'],
      $obj->fb_fieldLabels[$obj->userFields['pwd']]);
		  $obj->fb_excludeFromAutoRules[]=$obj->userFields['pwd'];
	}
	function postGenerateForm(&$form,&$fb,&$obj) {
        if($form->elementExists($fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix)) {

            $pwd = $form->getElement($fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix);
            $pwd->setLabel($obj->fb_fieldLabels[$obj->userFields['pwd']].__('(Vérification)'));
            $pwd->setValue('');
    	      $pwd2 = HTML_QuickForm::createElement('password',$fb->elementNamePrefix.$obj->userFields['pwd'].'2'.$fb->elementNamePostfix,
              $obj->fb_fieldLabels[$obj->userFields['pwd']]);

            if(empty($obj->id)) {
              $form->addRule($fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix,__('Veuillez entrer un mot de passe'),'required');
              $form->addRule($fb->elementNamePrefix.$obj->userFields['pwd'].'2'.$fb->elementNamePostfix,__('Veuillez retaper votre mot de passe'),'required');
            }
            $form->insertElementBefore($pwd2,$fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix);
            $form->addRule(array($fb->elementNamePrefix.$obj->userFields['pwd'].'2'.$fb->elementNamePostfix,$fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix),__('Vos deux mots de passe ne correspondent pas'),'compare');
            $form->addFormRule(array($this,'checkUniqueLogin'));
            $this->_obj = $fb->_do;
        }
	}
	function checkUniqueLogin($values) {
        $test = DB_DataObject::factory($this->_obj->tableName());
        $test->whereAdd("id!='".$this->_obj->id."'");
        $test->{$test->userFields['login']} = $values[$test->userFields['login']];
        if($test->find(true)) {
	        return array($test->userFields['login']=>__('Cet identifiant existe déjà, veuillez en choisir un autre SVP'));
	    }
        return true;
	}
	function preProcessForm(&$values,&$fb,&$obj) {
        if(!is_array($fb->userEditableFields) || count($fb->userEditableFields==0)) {
            $fb->userEditableFields = array_keys($obj->table());
        }
	    if(empty($values[$fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix])) {
	        if($index = array_search($obj->userFields['pwd'],$fb->userEditableFields)) {
	            unset($fb->userEditableFields[$index]);
	        }
	    } elseif($obj->userFields['passEncryption']) {
	        $values[$fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix] = call_user_func($obj->userFields['passEncryption'],$values[$fb->elementNamePrefix.$obj->userFields['pwd'].$fb->elementNamePostfix]);
	    }
	}
    function prepareForLogin(&$obj,$reminder=true,$register=true)
    {
        $this->_addregister = $register;
        $this->_addreminder = $reminder;
        
        if($register) {
            $obj->fb_formHeaderText = __('Déjà inscrit ?');
        } else {
            $obj->fb_formHeaderText = __('Identification');            
        }
        $obj->fb_submitText = '>> '.__('Valider');
        $this->_usercontext = $obj->userFields['context'];
        $obj->fb_fieldsToRender = array($obj->userFields['login'],$obj->userFields['pwd']);
        $obj->fb_preDefOrder = array($obj->userFields['login'],$obj->userFields['pwd']);
        $obj->fb_postGenerateFormCallback=array($this,'postGenerateLogin');
    }
    function prepareForReminder(&$obj)
    {
        $obj->fb_formHeaderText = 'Mot de passe perdu ?';
        $obj->fb_submitText = '>> Envoyer';
        $obj->fb_fieldsToRender = array($obj->userFields['email']);
        $obj->fb_postGenerateFormCallback=array($this,'postGenerateReminder');
    }
    function postGenerateLogin(&$form,&$fb) {
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
      $noaccountError = $this->_obj->userFields['noaccountError']?$this->_obj->userFields['noaccountError']:__('Ce compte n\'existe pas');
      $passwordError = $this->_obj->userFields['passwordError']?$this->_obj->userFields['passwordError']:__('Mot de passe incorrect');      
      $inactiveError = $this->_obj->userFields['inactiveError']?$this->_obj->userFields['inactiveError']:__('Ce compte n\'est pas actif');      

        if(User::getInstance($this->_usercontext)->login($values[$this->_obj->userFields['login']],$values[$this->_obj->userFields['pwd']])) {
            if(!empty($this->_obj->userFields['valid']) && !User::getInstance($this->_usercontext)->getDBDO()->{$this->_obj->userFields['valid']}) {
                User::getInstance($this->_usercontext)->logout();
                return array($this->_obj->userFields['login']=>$inactiveError);
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
                    return array($this->_obj->userFields['login']=>$noaccountError);
                    break;
                default:
                    return array($this->_obj->userFields['pwd']=>$passwordError);
                    break;
            }
        }
    }
    function setContext($c) {
      $this->_usercontext=$c;
    }
    function postGenerateReminder(&$form,&$fb) {
        $do = $fb->_do;
        $elt = HTML_QuickForm::createElement('static','remindertext','',__('Pour récupérer votre mot de passe entrez votre adresse email dans le champ ci-dessous, vous recevrez vos codes d\'accès par email.'));
        $form->insertElementBefore($elt,$fb->elementNamePrefix.$do->userFields['email'].$fb->elementNamePostfix);
    }

}