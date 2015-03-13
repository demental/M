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
* @author       Arnaud Sellenet <demental at github>

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
    && ($this->getOption('edit', $table) || $this->getOption('view', $table))) {
      $subController = new M_Office_EditRecord($table, $_REQUEST['record']);
      $this->assign('__action','edit');
      return;
    }

    if($this->getOption('view',$do->tableName())===TRUE) {
      $dg = &new M_Office_View_DOPaging($this);
      $this->assign('__listview','dopaging');
    } else {
      $class = 'M_Office_View_'.$this->getOption('view',$do->tableName());
      $dg = new $class($this);
      $this->assign('__listview',$this->getOption('view',$do->tableName()));
    }
    $dg->prepare($do,true,isset($searchWhere)?($paginate?true:false):true);
    $this->assign('dg',$dg);
    $this->assign('total',$total);
    $this->assign('pager',$dg->getPaging());
    $this->assign('fields',$dg->getFields());
    $this->assign('__action','showtable');
    $deleteForm = new MyQuickForm('showTableForm', 'post', M_Office_Util::getQueryParams(array(),array(),false), '_self', null, true);
    M_Office_Util::addHiddenFields($deleteForm, array(), true);
  }
  public function doExecAddrecord()
  {
    $do = $this->getDO();
    $form = new MyQuickForm('editform','POST',URL::get($this->_modulename.'/editrecord',$_GET));
    $fb = MyFB::create($do);
    $fb->fieldsToRender = $this->getConfig('fieldsindetail');
    $fb->useForm($form);
    $fb->getForm();
    $this->assignRef('form',$form);
  }
  public function doExecEditrecord()
  {
    $do = $this->getDO();
    if(!$do->get($_GET['record'])) {
      $this->redirect('error/404');
    }
    $form = new MyQuickForm('editform','POST',URL::get($this->_modulename.'/editrecord',$_GET));
    $fb = MyFB::create($do);
    $fb->fieldsToRender = $this->getConfig('fieldsindetail');
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
    $form = new MyQuickForm('searchform','GET',URL::get($this->_modulename.'/list'),'',null,true);
	  if(method_exists($do,'prepareSearchForm')){
      $do->prepareSearchForm();
    }
    $formBuilder = MyFB::create($do);

    $formBuilder->selectAddEmpty = array();
  	if(is_array($do->links())){
        foreach ($do->links() as $field => $link) {
          $formBuilder->selectAddEmpty[] = $field;
        }
  	}
		if(is_array($formBuilder->enumFields)){
			foreach ($formBuilder->enumFields as $field){
        $formBuilder->selectAddEmpty[] = $field;
			}
		}
    $formBuilder->linkNewValue = false;
      if($o=$do->getPlugin('ownership')) {
          if($o->userIsInAdminMode()) {
            $formBuilder->fieldsToRender[]=$do->ownerShipField;
            $formBuilder->fieldLabels[$do->ownerShipField]='géré par';

          }
      }
      $formBuilder->userEditableFields=$do->fb_fieldsToRender;
      $formBuilder->formHeaderText=__('Search');
      $formBuilder->submitText='>>';
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
