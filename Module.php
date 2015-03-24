<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Module
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Module class. This is one of the most used class in the framework, in combination with Dispatcher, it
 * represents the Controller layer.
 * Modules are created using the factory() method. A module can be used as a result of a user request
 * or as a component (using the c() method in Mtpl)
 * this class also provides caching mechanism and user credentials
 *
 */
class Module extends Maman {
	/**
	 *
	 * description
	 *
	 * @var		unknown_type
	 * @access	protected
	 */
	protected $view;

	/**
	 *
	 * description
	 *
	 * @var		unknown_type
	 * @access	protected
	 */
	public $currentAction;

	/**
	 *
	 * description
	 *
	 * @var		unknown_type
	 * @access	protected
	 */
	private $_lastOutput;

	/**
	 *
	 * description
	 *
	 * @var		unknown_type
	 * @access	protected
	 */
	protected $_cachedData = null;

	/**
	 *
	 * description
	 *
	 * @param $modulename
	 * @return unknown_type
	 */
	function __construct($modulename)
	{
		$this->_modulename=$modulename;
		$this->_lastOutput = $this;
	}

	/**
	 *
	 * description
	 *
	 * @return unknown_type
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 *
	 * description
	 *
	 * @param $modulename
	 * @param $path
	 * @param $params
	 * @return unknown_type
	 */
	public static function &factory($modulename,$path=null,$params = null)
	{

		if(empty($path)) {
			$path=array('modules');
		}
		if(!is_array($path)) {
			$path = array($path);
		}
    Log::info('Module::factory '.$modulename);
    $plugmod = explode(':',$modulename);
    $moduleOpt = PEAR::getStaticProperty('Module','global');
    $optionsGroup = PEAR::getStaticProperty('Options','group');

    if($plugmod[1]) {

      Log::info('Calling plugin module '.$modulename);
      PluginRegistry::initPlugin($plugmod[0]);
      $path = array(APP_ROOT.'app/plugins/'.$plugmod[0].'/modules/','plugins/'.$plugmod[0].'/modules/');
      $moduleOpt['template_dir'][] = 'plugins/'.$plugmod[0].'/templates/';
      $moduleOpt['template_dir'][] = APP_ROOT.'app/plugins/'.$plugmod[0].'/templates/';
      $modulename = $plugmod[1];
		  $className = $plugmod[0].'_Module_'.$modulename;
    } else {
      $className = 'Module_'.$modulename;
    }
		$i=false;
		foreach($path as $aPath) {
			if (@include_once $aPath.'/'.$modulename.'.php') {
				$i=true;
				break;
			}
		}

		if (!$i)
		{
    Log::info('Module::factory '.$modulename.' not found in path '.implode(',',$path));
			throw new Error404Exception("No $modulename module in path ".implode(',',$path));
		}
    Log::info('Module::factory '.$modulename.' OK');

		$module = new $className($modulename);
		$module->_path=$path;
		Log::info('Generating options');
    $options = $module->generateOptions($moduleOpt, $optionsGroup);
		$module->setConfig($options);
		$module->setParams($params);
		$module->startView();
    Log::info('Module::factory '.$className.' configured');
		return $module;
	}

	/**
	 * Getters / Setters
	 *
	 **/
	/**
	 *
	 * description
	 *
	 * @return unknown_type
	 */
	protected function generateOptions($opt, $group = NULL)
	{
		$opt = array('all'=>$opt);
		$options = array(
        'caching' =>(MODE=='developpement'?false:true),
        'cacheDir' => $opt['all']['cacheDir'].'/config/',
        'lifeTime' => 72000,//TODO make configurable...
        'fileNameProtection'=>false,
        'automaticSerialization'=>true
		);

    Log::info('preparing options');
		$optcache = new Cache_Lite($options);
		if (empty($group)) { $group = 'default'; }
		if( !$moduleopt = $optcache->get(get_class($this), $group) )  {
      Log::info('no cache for options, live generating');
			foreach($this->_path as $path) {
				if (@include $path.'/'.$this->_modulename.'.conf.php')
				{
					Log::info('loading module config file');
					if(!is_array($config)) {$config=array();}
					$moduleopt = MArray::array_merge_recursive_unique($opt, $config);
					break;
				} else {
					$moduleopt=$opt;
				}
        Log::info('no cache for options, live generating');
	 	  }
	    if(MODE!='developpement')
	    {
	     	$ret = $optcache->save($moduleopt);
	     	Log::info('group = '.$group);
	     	if ($ret) {Log::info('options saved');}
	    }
		}
    Log::info('options prepared');
		return $moduleopt;
	}

	/**
	 *
	 * description
	 *
	 * @param $params
	 * @return unknown_type
	 */
	public function setParams($params)
	{
		if(!is_array($params)) {
			$params = array();
		}
		$this->_params = $params;
	}

	/**
	 *
	 * returns the parameter that was passed to $this using setParam() or setParams()
	 * handy when you insert a component in a template file (@see Mtpl::c ) with parameters passed to it
	 *
	 * @param string name of the param
	 * @return mixed
	 */
	public function getParam($value)
	{
		return $this->_params[$value];
	}

	/**
	 *
	 * returns the parameter that was passed to $this using setParam() or setParams()
	 * or, if it's not set, returns the request value
	 * handy when you want to use an action both as a component and as a page (this is a common-use with UOJS)
	 *
	 * @param string name of the param
	 * @return mixed
	 */
	public function getParamOrRequest($value)
	{
    if(key_exists($value,$this->_params)) {
      return $this->_params[$value];
    }
    return $this->getRequestParam($value);
	}

	/**
	 *
	 * description
	 *
	 * @param $var
	 * @param $val
	 * @return unknown_type
	 */
	public function setParam($var,$val)
	{
		$this->_params[$var] = $val;
	}

	/**
	 *
	 * description
	 *
	 * @return unknown_type
	 */
	public function startView()
	{
		$this->view = new Mtpl(M::getPaths('template'),$this);
		if($vars = $this->getconfig('templateVars',$action)) {
			$this->view->assignArray($vars);
		}

	}

	/**
	 *
	 * description
	 *
	 * @return unknown_type
	 */
	public function getCurrentAction()
	{
		return $this->currentAction;
	}

	/**
	 * Returns the name of the current module
	 */
	 public function getCurrentModule()
	 {
	   return $this->_modulename;
	 }
	/**
	 *
	 * description
	 *
	 * @param $action
	 * @return unknown_type
	 */
	public function setCurrentAction($action)
	{
		$this->currentAction=$action;
	}

	/**
	 *
	 * description
	 *
	 * @param $val
	 * @param $action
	 * @return unknown_type
	 */
	public function hasLayout ($val = null,$action = 'all')
	{
		if($val === NULL) {
			return $this->getConfig('layout' , $action);
		} elseif($val == TRUE) {
			Log::info('Default Layout for '.get_class($this));
			$this->setConfigValue('layout', 'index' , $action);
		} else {
			Log::info('No layout for '.get_class($this));
			$this->setConfigValue('layout', '__self' , $action);
		}

	}

	// Determines if an action can be executed
	// If so, (forces) executes the action or throws an exception
	/**
	 *
	 * description
	 *
	 * @param $action
	 * @return unknown_type
	 */
	public function executeAction($action)
	{
		$meth = 'doExec'.$action;
    Log::info('trying Module::'.$meth);
		if(!method_exists($this,$meth))
		{

			throw new Error404Exception($action.' not implemented in Module : '.get_class($this));
		}

		$conf = $this->getConfig('security',$action);
		$disabled = $this->getConfig('disabled',$action);
		if($disabled) {
			throw new Error404Exception($action.' is disabled in Module : '.get_class($this));
		}
		if (is_array($conf))
		{
			foreach($conf as $alevel) {
				if(User::getInstance($alevel)->isLoggedIn()) {
					$userok=true;
					break;
				}
			}
			if ($userok)
			{
				$this->setCurrentAction($action);
				if($vars = $this->getconfig('templateVars',$action)) {
					$this->view->assignArray($vars);
				}
				$this->forceExecute($action);
			}
			else
			{
				throw new SecurityException('Not enough credential to enter here');
			}
		}
		else
		{
      Log::info('No security nor disabled config');
			$this->setCurrentAction($action);

			if($vars = $this->getconfig('templateVars',$action)) {
				$this->view->assignArray($vars);
			}
      Log::info('vars assigned to view');
      Log::info($action.' forcing execution');
			$this->forceExecute($action);
      Log::info($action.' was forced for execution');
		}
	}

	// These two abstract methods are called right before and after the action is executed
	/**
	 *
	 * description
	 *
	 * @param $action
	 * @return unknown_type
	 */
	public function preExecuteAction($action)
	{
	}

	/**
	 *
	 * description
	 *
	 * @param $action
	 * @return unknown_type
	 */
	public function postExecuteAction($action)
	{
	}

	// Dumbly executes an action and checks for caching
	// Sets up environment if not cached
	// @param string action name
	// @return void
	/**
	 *
	 * description
	 *
	 * @param $action
	 * @return unknown_type
	 */
	protected function forceExecute($action)
	{
		$meth = 'doExec'.$action;
		$options = array(
        'caching' =>$this->getConfig('caching', $action),
        'cacheDir' => $this->getConfig('cacheDir',$action),
        'lifeTime' => $this->getConfig('cacheTime', $action, 7200),
        'fileNameProtection'=>false,

		);
		$this->cache = new Cache_Lite($options);

		if($cache_id = $this->getCacheId($action)) {
		        Log::info($action.' is cachable with cacheId = '.$cache_id);
			if($this->_cachedData = $this->cache->get($cache_id.'_'.($this->isAjaxRequest()?'ajax':'')))  {
        Log::info($action.' is retreived from cache with cacheId = '.$cache_id);
				return;
			}
		}
    Log::info($action.' is not in cache');
    //	Mreg::get('setup')->setUpEnv();
		// !gestion template_dir rajouté dans le setup coupons
		Mreg::get('setup')->setUpEnv();
    $optionsEnv = & PEAR :: getStaticProperty('Module', 'global');
    $optionsThis = $this->config;
    $diff = array_diff( $optionsEnv['template_dir'], $optionsThis['all']['template_dir']);
    if (is_array($diff))
    {
      foreach($diff as $k => $v)
      {
        $this->view->addPath($v,'after');
      }
		}

    Log::info('env setup. launching preExecute');
		$this->preExecuteAction($action);
    Log::info('preExecute launched. Launching '.get_class($this).'::'.$meth);
		call_user_func(array($this,$meth));
    Log::info('doExec launched');
		$this->postExecuteAction($action);
	}

	// Builds a cache identifier for the requested module/action.
	// if context does not allow caching, returns false.
	// this method should be overridden in modules if the developer wants more specific conditions and ID's
	// @params string the name of the action.
	// @returns string or false if no caching
	// ==============================================================
	/**
	 *
	 * description
	 *
	 * @param $action
	 * @return unknown_type
	 */
	public function getCacheId($action)
	{
		if(count($_POST)==0) {
			$hash = $this->_modulename.'_'.$action.'_'.T::getLang().'_'.md5(print_r($_GET,true));

			return $hash;
		}
		return false;
	}

	/**
	 *
	 * Forces the current action to use the $tpl template instead of its default one (templates/moduleName/actionName.php)
	 *
	 * @param $tpl string path to the desired template (module/action)
	 */
	public function setTemplate($tpl)
	{
		$this->_lastOutput->setConfigValue('template',$tpl);
	}
	/**
	 *
	 * Forces the current action to be decorated with the $tpl layout instead of the one defined by default (templates/index.php or the one defined in the modules/modulename.conf.php config file)
	 *
	 * @param $tpl string path to the desired layout
	 */
	public function setLayout($tpl)
	{
		$this->_lastOutput->setConfigValue('layout',$tpl);
	}

	/**
	 * manually set output, which bypassed the view layer.
	 */
	public function setOuput($output)
	{
		$this->_output_processed = true;
		$this->_processed_output = $output;
	}
	/**
	 *
	 * description
	 *
	 * @param $template
	 * @param $layout
	 * @return unknown_type
	 */
	public function output($template=null,$layout=null)
	{
		if($this->_output_processed) {
			return $this->_processed_output;
		}

		if($this->_cachedData) {
			return $this->_cachedData;
		}

		$a=$this->_lastOutput->getCurrentAction();

		if(is_null($template)) {
			$template = $this->_lastOutput->getConfig('template',$a)?
			$this->_lastOutput->getConfig('template',$a):
			  strtolower(preg_replace('`^(.*Module_)`i', '', get_class($this->_lastOutput))).'/'.$a;

			Log::info('Setting template '.$template.' for module '.get_class($this->_lastOutput));
		}

		if($template=='__none') {
			return;
		}

		if(!is_array($template)) {
			$template = array($template);
		}

		if(is_null($layout)) {
			$layout = $this->_lastOutput->getConfig('layout',$a)?
			$this->_lastOutput->getConfig('layout',$a):
							'index';
		}
		Log::info('Setting layout '.$layout.' for module '.get_class($this->_lastOutput));

		if($this->isAjaxRequest()) {
			$layout='__self';
			$t2=$template;
			foreach($t2 as $t) {
				array_unshift($template,$t.'.bloc');
			}
		}
		if($this->isComponent()) {
		  $layout='__self';
		} else {
		  // Module is rendered as page,
  		// Let's add postFilters if some are provided in the postFilters configuration key
      $postFilters = $this->getConfig('postfilters',$a);
      if(is_array($postFilters)) {
        foreach($postFilters as $filter) {
          $this->_lastOutput->view->addPostFilter($filter);
        }
      }
	  }

		if($layout=='__self'){
			Log::info('Displaying selfsufficient for module '.get_class($this->_lastOutput));
			$ret = $this->_lastOutput->view->fetch($template);

		} else {
			// Sinon c'est que le layout posséde une variable $__action qui est utilisé pour inclure le template de l'action
			Log::info('Decorate module '.get_class($this->_lastOutput).' with '.$layout);

			$this->_lastOutput->view->assign("__action", $template);

			$ret = $this->_lastOutput->view->fetch($layout);

		}
    if(MODE!='developpement' && is_a($this->cache,'Cache_Lite')) $this->cache->save($ret);
    $this->_output_processed = true;
    $this->_processed_output = $ret;
		return $ret;
	}

	public function isComponent($bool = null)
	{
    if(!is_null($bool)) {
      $this->__isComponent = $bool;
    }
    return $this->__isComponent;

	}

	/**
	 * returns the original action name requested by the enduser
	 * @return string
	 */
	public function getRequestedAction()
	{
		return ($_REQUEST['action']);
	}

	/**
	 * returns the request value for the $val key
	 * @param string $val key name
	 * @return mixed
	 */
	public function getRequestParam($val)
	{
		return ($_REQUEST[$val]);
	}

	/**
	 * returns the get value for the $val key
	 * @param string $val key name
	 * @return mixed
	 */
	public function getGetParam($val)
	{
		return ($_GET[$val]);
	}

	/**
	 * returns the post value for the $val key
	 * @param string $val key name
	 * @return mixed
	 */
	public function getPostParam($val)
	{
		return ($_POST[$val]);
	}


	/**
	 * handles exceptions that are not 404 or 403
	 */
	public function handleException($e)
	{
		if(MODE == 'production') {
	    $this->executeAction('index');
	  } else {
	  	$out = '<h3>Damn, an exception occured !</h3>';
	  	$out .= '<p>'.$e->getMessage().'</p>';
	  	$out .= '<pre>';
	  	$out .= $this->stackTrace($e->getTrace());
	  	$out .= '</pre>';
	  	$this->setOuput($out);
	  }
	}
	/**
	 * wether the request has a value
	 * @param $val key name
	 * @return bool
	 */
	public function hasRequest($val)
	{
		return key_exists($val,$_REQUEST);
	}

	/**
	 * Proxy methods for the view
	 **/
	/**
	 *
	 * description
	 *
	 * @param $var
	 * @param $val
	 * @return unknown_type
	 */
	public function assign($var, $val) {
		$this->view->assign($var,$val);
	}

	/**
	 *
	 * description
	 *
	 * @param $var
	 * @param $val
	 * @return unknown_type
	 */
	public function append($var, $val) {
		$this->view->append($var,$val);
	}

	/**
	 *
	 * description
	 *
	 * @param $var
	 * @param $val
	 * @return unknown_type
	 */
	public function assignRef($var, &$val) {
		$this->view->assignRef($var,$val);
	}

	/**
	 *
	 * description
	 *
	 * @param $module
	 * @param $action
	 * @return unknown_type
	 */
	public function forward($module,$action='index', $params = false) {
		if($this->isComponent()) {
			$d = new Component($module,$action, $params ? $params  : $this->_params);
		} else {
			$d = new Dispatcher($module,$action, $params ? $params  : $this->_params);
		}
		$d->execute();
		$this->_lastOutput = $d->getPage();
	}

	/**
	 *
	 * description
	 *
	 * @param $modulaction
	 * @param $vars
	 * @param $lang
	 * @param $secure
	 * @return unknown_type
	 */
	public function redirect($modulaction,$vars = null,$lang=null,$secure=null,$status = '302') {
		if(preg_match('`^(http|https)://`i',$modulaction)) {
			header('location:'.$modulaction,true,$status);
			exit(0);
		}

    if($this->isComponent()) {
      list($module,$action)=explode('/',$modulaction);
      $varsAr = is_null($vars) ? $this->_params : array_merge($vars,$this->_params);
      return $this->forward($module[0],$module[1]?$module[1]:'index', $varAr);
    }
		if($this->isAjaxRequest()) {
      $vars['__ajax']=1;
    }
		$url = URL::get($modulaction,$vars,$lang,$secure);
		if ((stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') && stristr($_SERVER['HTTP_USER_AGENT'], 'Mac')) || headers_sent()) {

			echo '<script language="JavaScript1.1">
      <!--
      location.replace("'.$url.'");
      //-->
      </script>
      <noscript>
      <meta http-equiv="Refresh" content="0; URL='.$url.'"/>
      </noscript>
      Redirection... merci de patienter ou cliquer <a href="'.$url.'">ici</a>.
      ';
		} else {
			header('location:'.$url,true,$status);
		}
		flush();
		exit(0);

	}
	public function redirect301($modulaction,$vars = null,$lang=null,$secure=null)
	{
	  $this->addHeader('301 Moved Permanently');
    $this->redirect($modulaction,$vars,$lang,$secure,'301');
	}

	public function redirect404($modulaction,$vars = null,$lang=null,$secure=null)
	{
    header('Status: 404');
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
    $arr = explode('/',$modulaction);
    $d = new Dispatcher($arr[0],$arr[1],$this->_params);
		$d->execute();
		echo $d->display();
		exit(0);
	}
  public function addHeader($header)
  {
    if($this->isComponent()) return;
    if (php_sapi_name()=='cgi') {
        header('Status: '.$header);
    } else {
        header('HTTP/1.1 '.$header);
    }
  }
	/**
	 *
	 * description
	 *
	 * @param $formName
	 * @param $method
	 * @param $action
	 * @param $target
	 * @param $attributes
	 * @param $trackSubmit
	 * @return unknown_type
	 */
	public function createForm($formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false)
	{
		if(empty($action)) {
			$action = URL::getSelf();
		}
		return new MyQuickForm($formName,$method,$action,$target,$attributes,$trackSubmit);
	}

	/**
	 * Returns true if the current request is a XmlHttpRequest
	 *
	 * @return bool
	 */
	public function isAjaxRequest() {
		return array_key_exists('X-PJAX',$_SERVER) || (array_key_exists('HTTP_X_REQUESTED_WITH',$_SERVER) && $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest');
	}

  /**
   * Events triggering
   */
  public function trigger($eventName,$params)
  {
    # code...
  }
  /**
   * stores flash message, released until displayed
   * @param string the message
   * @param string the message type (may be info, error, warning, success, @see http://twitter.github.com/bootstrap/ for alert types)
   * defaults to info
   *
   */
   public function flash($message,$type='info')
   {
     if(!$_SESSION) session_start();
     $_SESSION['flash'][$type][]=$message;
   }
   public function popflash($type='info')
   {
     if(!$_SESSION) session_start();
     if(!is_array($_SESSION['flash'][$type])) return false;
     return array_shift($_SESSION['flash'][$type]);
   }

   public function accept_methods()
   {
   	$args = func_get_args();
   	$requested_method = strtolower($_SERVER['REQUEST_METHOD']);
   	foreach($args as $allowed_method) {
   		if($requested_method == strtolower($allowed_method)) {
   			return true;
   		}
   	}
   	throw new InvalidRequestException('Method '.$requested_method.' not allowed');
  }
}
