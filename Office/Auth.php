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
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'M/Office/Controller.php';
require_once 'M/MyFB.php';

class M_Office_Auth extends M_Office_Controller {
    public function __construct() {
      parent::__construct();        
  		if(key_exists('logout',$_REQUEST)){
  		    User::getInstance('office')->logout();
  			  M_Office_Util::refresh();
  		}
  		if(User::getInstance('office')->isLoggedIn()){
  			if(!User::getInstance('office')->hasProperty('privileges')){
          Mreg::get('authHelper')->initUserPrivileges();
  		  }
        $privileges = User::getInstance('office')->getProperty('privileges');
        $showTableOptions = & PEAR::getStaticProperty('m_office_showtable', 'options');
        $editRecordOptions = & PEAR::getStaticProperty('m_office_editrecord','options');
        $chooseTableOptions =& PEAR::getStaticProperty('m_office_choosetable', 'options');
        $homeOptions =& PEAR::getStaticProperty('m_office_frontendhome', 'options');

        $chooseTableOptions= $privileges['choosetable'];
        $showTableOptions = $privileges['showtable'];
        $editRecordOptions= $privileges['editrecord'];
        $homeOptions = $privileges['frontendhome'];
	    } else {

            $this->loginForm(User::getInstance('office')->containers['office']['table']);
      }
    }
    public function loginForm($table) {
	    $form = new HTML_QuickForm('loginForm', 'POST', M_Office_Util::getQueryParams(array(), array(), false), '_self', null, true);
	    $authDO=& DB_DataObject::factory($table);

      $authDO->prepareForLogin(false,false);
	    $authFB=& MyFB::create($authDO);
	    $authFB->useForm($form);
	    $authFB->getForm();
  		if($form->validate()){
  			M_Office_Util::refresh($_SERVER['REQUEST_URI']);
  		}
	    M_Office::$dsp='login';
      $this->assign('loginForm',$form);
    }
  	public function initOptions() {
  		$chooseTableOptions =& PEAR::getStaticProperty('M_Office_choosetable', 'options');
  		$chooseTableOptions=$_SESSION['adminPrivileges']['choosetable'];
  		$showTableOptions =& PEAR::getStaticProperty('M_Office_showtable', 'options');
  		$showTableOptions=$_SESSION['adminPrivileges']['showtable'];
  		$editRecordOptions =& PEAR::getStaticProperty('M_Office_editrecord', 'options');
  		$editRecordOptions=$_SESSION['adminPrivileges']['editrecord'];
  	}
  	public function regenerateModList() {
  	  Mreg::get('authHelper')->regenerateModList();
  	}
}
