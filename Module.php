<?php
// =========================================================================================================
// = Module class. This is one of the most used class in the framework, in combination with Dispatcher, it
// = represents the Controller layer.
// = Modules are created using the factory() method. A module can be used as a result of a user request 
// = or as a component (using the c() method in Mtpl) 
// = this class also provides caching mechanism and user credentials  
// =========================================================================================================
class Module extends Maman {
  	protected $view;
  	public $currentAction;
    private $_lastOutput;
    protected $_cachedData = null;
  function __construct($modulename) 
  {
    $this->_modulename=$modulename;
    $this->_lastOutput = $this;
  }
  public function getView()
  {
    return $this->view;
  }
  public static function &factory($modulename,$path=null,$params = null)
  {
    if(empty($path)) {
      $path=array('modules');
    }
    if(!is_array($path)) {
      $path = array($path);
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
	 		throw new Error404Exception("No $modulename module in path ".implode(',',$path));
	 	}

  	  $nommodule = 'Module_'.$modulename;
   	  $module = new $nommodule($modulename);
      $module->_path=$path;
      $module->setConfig($module->generateOptions());      
      $module->setParams($params);
      $module->startView();
  	  return $module;
  }

  /**
   * Getters / Setters
   *
   **/
  protected function generateOptions()
  {
    $opt = array('all'=>PEAR::getStaticProperty('Module', 'global'));
    $options = array(
        'caching' =>(MODE=='developpement'?false:true),
        'cacheDir' => $opt['all']['cacheDir'].'/config/',
        'lifeTime' => 72000,//TODO affiner ça...
        'fileNameProtection'=>false,
        'automaticSerialization'=>true
    );

    $optcache = new Cache_Lite($options);
    if(!$moduleopt = $optcache->get($this->_modulename))  {
      foreach($this->_path as $path) {
  	 	if (@include $path.'/'.$this->_modulename.'.conf.php')
  	 	{
  	 	  
        if(!is_array($config)) {$config=array();}
 		    $moduleopt = MArray::array_merge_recursive_unique($opt, $config);
        break;
  	 	} else {
  	 	  $moduleopt=$opt;
  	 	}
	 	}
	 	$optcache->save($moduleopt);
  }

    return $moduleopt;
  }
  public function setParams($params)
  {
    if(!is_array($params)) {
      $params = array();
    }
    $this->_params = $params;
  }
  public function getParam($value)
  {
    return $this->_params[$value];
  }
  public function setParam($var,$val)
  {
    $this->_params[$var] = $val;
  }
  public function startView()
  {
    $this->view = new Mtpl($this->getConfig('template_dir'),$this);
		if($vars = $this->getconfig('templateVars',$action)) {
      $this->view->assignArray($vars);
    }
    
  }
  public function getCurrentAction()
  {
    	return $this->currentAction;
  }
  
  public function setCurrentAction($action)
  {
    	$this->currentAction=$action;
  }
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
  
  public function executeAction($action)
  {
        $meth = 'doExec'.$action;
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
    		$this->setCurrentAction($action);
    		if($vars = $this->getconfig('templateVars',$action)) {
          $this->view->assignArray($vars);
        }
        
        $this->forceExecute($action);
    	}
  } 
  
  // These two abstract methods are called right before and after the action is executed
  
  public function preExecuteAction($action)
  {
  } 
  public function postExecuteAction($action)
  {
  } 
  // Dumbly executes an action and checks for caching
  // Sets up environment if not cached
  // @param string action name
  // @return void
  //
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
      if($this->_cachedData = $this->cache->get($cache_id.'_'.($this->isAjaxRequest()?'ajax':'')))  {
        return;
      }
    }
    Mreg::get('setup')->setUpEnv();
    $this->preExecuteAction($action);
    call_user_func(array($this,$meth));
    $this->postExecuteAction($action);      
  }

  // Builds a cache identifier for the requested module/action.
  // if context does not allow caching, returns false.
  // this method should be overridden in modules if the developer wants more specific conditions and ID's
  // @params string the name of the action.
  // @returns string or false if no caching
  // ==============================================================

  public function getCacheId($action)
  {
    if(count($_POST)==0) {
      $hash = $this->_modulename.'_'.$action.'_'.T::getLang().'_'.md5(print_r($_GET,true));

      return $hash;
    }
    return false;
  }
  public function setTemplate($tpl)
  {
    $this->_lastOutput->setConfigValue('template',$tpl);
  }
  public function output($template=null,$layout=null)
  {


    if($this->_cachedData) {
      return $this->_cachedData;
    }

		$a=$this->_lastOutput->getCurrentAction();

		// Le fichier de template de l'action est-il celui par défaut (nom_de_l_action.tpl) ou a-t-il été spécifié
		// dans la configuration du module ?
    if(is_null($template)) {
  		$template = $this->_lastOutput->getConfig('template',$a)?
  							$this->_lastOutput->getConfig('template',$a):
  							strtolower(str_replace('Module_', '', get_class($this->_lastOutput))).'/'.$a;
          Log::info('Setting template '.$template.' for module '.get_class($this->_lastOutput));
    }
		if($template=='__none') {
		    return;
		}
    if(!is_array($template)) {
      $template = array($template);
    }
		// Le fichier de template englobant est-il celui par défaut (index.tpl) ou a-t-il été spécifié
		// dans la configuration du module ?
    if(is_null($layout)) {
		$layout = $this->_lastOutput->getConfig('layout',$a)?
							$this->_lastOutput->getConfig('layout',$a):
							'index';
    }						
        Log::info('Setting layout '.$layout.' for module '.get_class($this->_lastOutput));

		// Si on veut afficher uniquement le template de l'action, sans aucun layout on utilise le mot clé __self dans le 
		// fichier de configuration du module
    if($this->isAjaxRequest()) {
      $layout='__self';
      $t2=$template;
      foreach($t2 as $t) {
        array_unshift($template,$t.'.bloc');
      }
    }
		if($layout=='__self'){
            Log::info('Displaying selfsufficient for module '.get_class($this->_lastOutput));
            $ret = $this->_lastOutput->view->fetch($template);

		} else {
		// Sinon c'est que le layout possède une variable $__action qui est utilisé pour inclure le template de l'action 	
            Log::info('Decorate module '.get_class($this->_lastOutput).' with '.$layout);

			$this->_lastOutput->view->assign("__action", $template);

      $ret = $this->_lastOutput->view->fetch($layout);

		}

    $this->cache->save($ret);
    return $ret;
  }
  /**
   * TODO pouvoir récupérer n'importe quelle valeur de $_REQUEST
   **/
  public function getRequest()
  {
	return ($_REQUEST['action']);
  }
  public function getRequestParam($val)
  {
	return ($_REQUEST[$val]);
  }

  public function hasRequest($val)
  {
    return key_exists($val,$_REQUEST);
  }
  
  /**
   * Proxy methods for the view
   **/
  public function assign($var, $val) {
      $this->view->assign($var,$val);
  }
  public function append($var, $val) {
      $this->view->append($var,$val);
  }
  public function assignRef($var, &$val) {
      $this->view->assignRef($var,$val);
  }
  public function forward($module,$action) {
      $d = new Dispatcher($module,$action,$this->_params);
      $d->execute();
      $this->_lastOutput = $d->getPage();
  }
  public function redirect($modulaction,$vars = null,$lang=null,$secure=null) {
      if(eregi('^(http|https)://',$modulaction)) {
          header('location:'.$modulaction);
        exit;
      }
      header('location:'.URL::get($modulaction,$vars,$lang,$secure));
      exit;
  }
  public function createForm($formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false)
  {
    if(empty($action)) {
      $action = URL::getSelf();
    }
    return new MyQuickForm($formName,$method,$action,$target,$attributes,$trackSubmit);
  }
  public function isAjaxRequest() {
      return $_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest';
  }  
 
}