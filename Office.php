<?php
/**
 * M PHP Framework
 *
 *
 * @package      M
 * @subpackage   M_Office
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

require 'M/Office/Controller.php';
require 'M/Notifier.php';
require 'M/Office/Icons.php';
if(!defined('NORMALUSER')) {
	define('NORMALUSER',0);
}
if(!defined('ADMINUSER')) {
	define('ADMINUSER',1);
}
if(!defined('ROOTUSER')) {
	define('ROOTUSER',2);
}
if(!defined('ROOT_ADMIN_SCRIPT')) {
	define('ROOT_ADMIN_SCRIPT','index.php');
}
if(!defined('OFFICE_TEMPLATES_FOLDER')) {
	define('OFFICE_TEMPLATES_FOLDER', realpath(dirname(__FILE__)).'/Office/Templates/');
}

$dispatchopt = &PEAR::getStaticProperty('Dispatcher', 'global');
//$dispatchopt['all']['modulepath'][]='M/Office/modules/';

/**
 *
 * "Office" application dispatcher
 * The office application is one of the most powerful features of the M framework.
 * It's also the one that needs most refactoring
 * This was originally base upon Justin Patrin's PEAR_DB_DataObject_FormBuilder_Frontend
 *
 *
 */
class M_Office extends M_Office_Controller implements iListener {
	public static $dsp='__defaut/index';

	public function __construct($layout = 'main') {

		parent::__construct();
		M_Office_Util::$mainOptions = PEAR::getStaticProperty('m_office', 'options');

		$modinfo = &PEAR::getStaticProperty('Module','global');
		array_unshift($modinfo['template_dir'],APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR);
		array_push($modinfo['template_dir'],OFFICE_TEMPLATES_FOLDER);

		// TODO Check if requested module is valid

		$tpl = new Mtpl(array(

		OFFICE_TEMPLATES_FOLDER,
		APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
		APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
		APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$_REQUEST['module'].DIRECTORY_SEPARATOR,

		)
		);
		$tpl->assign('adminTitle',$this->getOption('adminTitle'));
		$tpl->assign('jsdir',SITE_URL.'js/');

		Mreg::set('tpl',$tpl);


		Mtpl::addJS(array('livesearch'));
		Mtpl::addCSS('livesearch');
		Mtpl::addJSinline('$("#chooseTable input").livesearch({autosearch:true,url:"'.ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT.'?livesearch=1"'.',minchar:2});','ready');
		$this->ajaxAuth=true;
		if($this->getOption('auth')){
			$this->ajaxAuth=false;
			require 'M/Office/Auth.php';
			$subController = new M_Office_Auth($_REQUEST['database']);
			if(!key_exists('adminPrivileges',$_SESSION) || key_exists('logout',$_REQUEST)){
				$this->assign('username',User::getInstance('office')->getProperty('username'));
				$this->ajaxAuth=true;
			} elseif(!User::getInstance('office')->isLoggedIn()) {
				$this->ajaxAuth=false;
			} else {
				$subController->initOptions();
				$this->assign('username',User::getInstance('office')->getProperty('username'));
			}
		}

		$not = Notifier::getInstance();
		$not->addListener($this);
		if($this->getOption('auth') && !User::getInstance('office')->isLoggedIn()) {
			return;
		}
		if(key_exists('updateSuccess',$_REQUEST)) {
			$this->say('Votre enregistrement a été modifié avec succès.');
			M_Office_Util::clearRequest(array('updateSuccess'=>1));
		}
		// TODO remove those old modules
		if(isset($_REQUEST['module'])) {
			$info = M_Office_Util::getModuleInfo($_REQUEST['module']);
			$module = $_REQUEST['module'];
		}
		if(!$info && eregi('^(.+)helper$',$_REQUEST['module'],$tab)) {
			$info = array('type'=>'dyn','title'=>'Assistant '.$tab[1]);
			$module = $tab[1];
		}

		if($this->isAjaxRequest() && $this->ajaxAuth && $info['type']!='dyn') {
			$this->output='';
			unset($this->localOutput);
		}
		if (isset($_REQUEST['regenerate']) && $this->getOption('regenerate')) {
			require 'M/Office/Generator.php';
			DB_DataObject::debugLevel(1);
			ini_set('display_errors',1);
			M_Office_Generator::regenerateSchema();
			try {
				$a = Mreg::get('authHelper');
				echo '<h1>'.__('Regenerating privileges matrix').'</h1>';
				$a->regenerateModList();
			} catch(Exception $e) {
				echo 'no authhelper, assuming no auth capable application';
			}
		}	elseif (isset($_REQUEST['debug'])) {
			$debug=(int)$_REQUEST['debug']%3;
			DB_DataObject::debugLevel($debug);
			ini_set('display_errors',1);
		}

		if($_REQUEST['livesearch']) {
			require 'M/Office/livesearch.php';
			$aj = new M_Office_livesearch($_REQUEST['searchtext'],$_REQUEST['expand']);
			$this->output = $aj->processRequest();

			return;
		} elseif($_REQUEST['treesort']) {
			require 'M/Office/treesort.php';
			$aj = new M_Office_treesort();
			$this->output=$aj->processRequest();
			return;
		} elseif($_REQUEST['liveedit']) {
			require 'M/Office/liveedit.php';
			$aj = new M_Office_liveedit($_REQUEST['liveedit']);
			$this->output=$aj->processRequest();
			return;
		} elseif(key_exists('ajaxfromtable',$_REQUEST)) {
			require 'M/Office/ajaxFromTable.php';
			$aj = new M_Office_ajaxFromTable($_REQUEST['module'],$_REQUEST['filterField'],$_REQUEST['filterValue']);
			$this->output = $aj->processRequest();
			return;
		}




		require 'M/Office/ChooseTable.php' ;
		$subController = new M_Office_ChooseTable();
		if(isset($_REQUEST['module'])) {
			if(!$info) {
				$info = M_Office_Util::getModuleInfo($_REQUEST['module']);
			}
			switch($info['type']) {
				case 'db':
					require 'M/Office/ShowTable.php';
					// TODO ajouter ce path en avant-dernier et non en dernier
					Mreg::get('tpl')->addPath(APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$info['table'].DIRECTORY_SEPARATOR,'after');
					$subController = new M_Office_ShowTable($_REQUEST['module'],$filter);
					break;
				case 'dyn':
				  /*@todo : use a dispatcher here*/
					$subController = Module::factory($_REQUEST['module'],array(APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR,APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'modules/','M/Office/modules/'));
          try {
					  $subController->executeAction($_REQUEST['action']?$_REQUEST['action']:'index');
          } catch (Error404Exception $e) {
            $subController->handleNotFound();
          }
					$this->assign('__action','dyn');
					$layout = $subController->getConfig('layout',$_REQUEST['action']?$_REQUEST['action']:'index');
					if($layout=='__self') {
						M_Office::$dsp='__defaut/ajaxindex';
					} elseif($layout) {
						M_Office::$dsp=$layout;
					}
					$this->assign('output',$subController->output(null,'__self'));
					break;
			}
			$this->assign('currentmodule',$_REQUEST['module']);
		} else {
			require_once 'M/Office/FrontEndHome.php' ;
			$subController = new M_Office_FrontEndHome();
		}
	}
	public function display() {
		echo $this->fetch();
		return;
	}
	public function fetch()
	{
		if(self::isAjaxRequest()) {
			M_Office::$dsp='__defaut/ajaxindex';
		}
    $tpl = Mreg::get('tpl');
		$tpl->assign('regenerate',$this->getOption('regenerate'));
		$tables = $this->getGlobalOption('searchInTables','frontendhome');
		$tpl->assign('messages',$_SESSION['flashmessages']);
		if(count($tables)==0) {
			$tpl->assign('showlivesearch',false);
		} else {
			$tpl->assign('showlivesearch',true);
		}
		return $tpl->fetch(M_Office::$dsp);

	}
  public function getEvents() {
    return array('notification');
  }
	public function handleEvent($sender,$event,$params = null) {
		$_SESSION['flashmessages'][]=array($params,$event);
	}
	public static function isAjaxRequest() {
		return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest' || $_REQUEST['__ajax'];
	}
	// ==============================
	// = Proxy (for faster writing) =
	// ==============================
	public static function URL($params = array(), $remove = array(), $entities = false)
	{
		if(!is_array($params)) {
			$tmp = explode('/',$params);
			$params = $remove;
			$params['module'] = $tmp[0];
			$params['action'] = $tmp[1];
			$remove = $entities;
			$entities = false;
		}
		return M_Office_Util::getQueryParams($params,$remove,$entities);
	}
}