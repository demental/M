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

class Office_DbModule extends Module {
  protected $_searchform;
  function __construct($name) {
    parent::__construct($name);
  }
  protected function getDO()
  {
    return $this->doForTable($this->_modulename);
  }
  public static function &factory($module,$path)
  {
    try {
     $mod = parent::factory($module,$path); 
    } catch (Exception $e) {
      $opt = PEAR::getStaticProperty('m_office','options');
      $modopt = $opt['modules'][$module];      
      $mod = new Office_DbModule($module);
      $mod->_path=$path;
      $mod->setConfig(array_merge($mod->generateOptions(),$modopt));
      $mod->startView();
    }
    return $mod;

  }
  protected function doForTable($table)
  {
    $do = DB_DataObject::factory($table);
    $fbOptions = $this->getConfig('fbOptions', $table, true);
    if (is_array($fbOptions)) {
        foreach ($fbOptions as $var => $value) {
            $do->{'fb_'.$var} = $value;
        }
    }		
		$AuthOptions = PEAR::getStaticProperty('m_office_auth', 'options');
		$viewOptions = PEAR::getStaticProperty('m_office_showtable', 'options');	
		if($AuthOptions['ownership']){

			$do->filterowner=User::getInstance('office')->getProperty('level')!=NORMALUSER?false:User::getInstance('office')->getProperty('groupId');

            if($p = &$do->getPlugin('ownership')) {
				$p->userIsInAdminMode(User::getInstance('office')->getProperty('level'));
			}
		}
		if($viewOptions['tableOptions'][$table]['filters']){
        	$do->whereAdd($viewOptions['tableOptions'][$table]['filters']);
  	}
    return $do;
  }
  protected function generateOptions()
  {
		$AuthOptions = PEAR::getStaticProperty('m_office', 'options');
    $userOpt = $AuthOptions['auth'];
		$opt = array('all'=>PEAR::getStaticProperty('Module', 'global'));

      $options = array(
          'caching' =>(MODE=='developpement'?false:true),
          'cacheDir' => $opt['all']['cacheDir'].'/config/'.($userOpt?User::getInstance('office')->getId().'/':''),
          'lifeTime' => null,
          'fileNameProtection'=>false,
          'automaticSerialization'=>true
      );
      $optcache = new Cache_Lite($options);
      if(!$moduleopt = $optcache->get($this->_modulename))  {
    	 	if (@include_once $this->_path.$this->_modulename.'.conf.php')
    	 	{
          if(!is_array($config)) {$config=array();}
   		      $moduleopt = MArray::array_merge_recursive_unique($opt, $config);
    	 	  } else {
    	 	    $moduleopt=$opt;
    	 	  }
        $useropt = Mreg::get('authHelper')->getPrivilegesForModule(User::getInstance('office'),$this->_modulename);
   		  $moduleopt = MArray::array_merge_recursive_unique($moduleopt, $useropt);        
    	 	$optcache->save($moduleopt);
      }
  	  return $moduleopt;
  }
  public function doExecIndex()
  {
    $this->forward('list');
  }
  public function doExecList()
  {
    $this->table=$table;
    if(!$this->getConfig('view',$table)){
      M_Office_Util::refresh(ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT);
    }
    $do =& $this->doForTable($table);
    if($this->getOption('search',$table)){
        $doSearch =& $this->doForTable($table);
        $searchForm=& M_Office_Util::getSearchForm($doSearch);
        $this->assign('search',$searchForm);
        if (isset($_REQUEST['searchSubmit'])) {
          $do = $this->getSearchDO($searchForm);
        }            
    }
    if($this->getAndProcessActions(clone($do),$table)) {
        return;
    }
    if (isset($_REQUEST['record']) 
    && ($this->getOption('edit', $table) || $this->getOption('view', $table) || $this->getOption('directEdit', $table))) {
      require 'M/Office/EditRecord.php';
      $subController = new M_Office_EditRecord($table, $_REQUEST['record']);
      $this->assign('__action','edit');
      return;
    }
          
    if($this->getOption('view',$do->tableName())===TRUE) {
      require 'M/Office/View/DOPaging.php';
      $dg = &new M_Office_View_DOPaging($this);
      $this->assign('__listview','dopaging');
    } else {
      $classfile = 'M/Office/View/'.$this->getOption('view',$do->tableName()).'.php';
      $class = 'M_Office_View_'.$this->getOption('view',$do->tableName());
      require $classfile;
      $dg = &new $class($this);
      $this->assign('__listview',$this->getOption('view',$do->tableName()));
    }
    $dg->prepare($do,true,isset($searchWhere)?($paginate?true:false):true);
    $this->assign('dg',$dg);
    $this->assign('total',$total);
    $this->assign('pager',$dg->getPaging());
    $this->assign('fields',$dg->getFields());
    $this->assign('__action','showtable');
    $deleteForm = new HTML_QuickForm('showTableForm', 'post', M_Office_Util::getQueryParams(array(),array(),false), '_self', null, true);
    M_Office_Util::addHiddenFields($deleteForm, array(), true); 
  }
  public function doExecAddrecord()
  {
    $do = $this->getDO();      
    $do->fb_fieldsToRender = $this->getConfig('fieldsindetail');
    $form = new HTML_QuickForm('editform','POST',URL::get($this->_modulename.'/editrecord',$_GET));
    $fb = MyFB::create($do);
    $fb->useForm($form);
    $fb->getForm();
    $this->assignRef('form',$form);
  }
  public function doExecEditrecord()
  {
    $do = $this->getDO();
    $do->fb_fieldsToRender = $this->getConfig('fieldsindetail');
    if(!$do->get($_GET['record'])) {
      $this->redirect('error/404');
    }
    $form = new HTML_QuickForm('editform','POST',URL::get($this->_modulename.'/editrecord',$_GET));
    $fb = MyFB::create($do);
    $fb->useForm($form);
    $fb->getForm();
    $this->assignRef('form',$form);
  }
  public function doExecDeleterecord()
  {
    // code...
  }
  public function doExecGlobalaction()
  {
    // code...
  }
  public function doExecBatchaction()
  {
    // code...
  }
  public function doExecSingleaction()
  {
    // code...
  }
  protected function &getSearchForm()
  {
    if(!$this->getConfig('search')) { return false; }

    if($this->_searchform) { return $this->_searchform; }
    $do = $this->getDO();
    $form = new HTML_QuickForm('searchform','GET',URL::get($this->_modulename.'/list'),'',null,true);
	  if(method_exists($do,'prepareSearchForm')){
      $do->prepareSearchForm();
    }

    $do->fb_selectAddEmpty = array();
  	if(is_array($do->links())){
        foreach ($do->links() as $field => $link) {
          $do->fb_selectAddEmpty[] = $field;
        }
  	}
		if(is_array($do->fb_enumFields)){
			foreach ($do->fb_enumFields as $field){
         $do->fb_selectAddEmpty[] = $field;
			}
		}
    $do->fb_linkNewValue = false;
      if($o=$do->getPlugin('ownership')) {
          if($o->userIsInAdminMode()) {
              $do->fb_fieldsToRender[]=$do->ownerShipField;
              $do->fb_fieldLabels[$do->ownerShipField]='géré par';

          }
      }
      $do->fb_userEditableFields=$do->fb_fieldsToRender;
      $do->fb_formHeaderText=__('Search');
      $do->fb_submitText='>>';
      $formBuilder =& MyFB::create($do);
      $formBuilder->preGenerateFormCallback='fake';
     	$formBuilder->useForm($form);
      $specialElements = array_keys($formBuilder->_getSpecialElementNames());
      foreach ($specialElements as $specialField) {
        unset($fieldsToRender[$specialField]);
      }

      $formBuilder->getForm();
      $form->addElement('hidden', 'searchSubmit', 1);
      $form->_rules = array();
      $form->_formRules = array();
      $form->_required = array();
      M_Office_Util::addHiddenFields($form, array('search', 'page'), true);
      $form->addElement('checkbox','__dontpaginate','',__('Afficher les résultats sur une seule page'));
      $this->_searchform=$form;
      return $this->_searchform;
  }
}