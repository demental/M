<?php
// ===============================================
// = For use in future refactoring of Office app =
// ===============================================

class Office_Module extends Module {
  function __construct($config) {

    parent::__construct($config);
  }
  public function executeAction($action)
  {

    $AuthOptions = PEAR::getStaticProperty('Office', 'global');
    $userOpt = $AuthOptions['auth'];
		if($userOpt && !User::getInstance('office')->isLoggedIn()) {
		  throw new SecurityException('Not enough credential to enter here (current credential = '.$userLevel.')');
	  }
	  return parent::executeAction($action);
  }
  protected function generateOptions()
  {
		$AuthOptions = PEAR::getStaticProperty('Office', 'global');
    $userOpt = $AuthOptions['auth'];
		$opt = array('all'=>PEAR::getStaticProperty('Module', 'global'));

      $options = array(
          'caching' =>(MODE=='developpement'?false:true),
          'cacheDir' => $opt['all']['cacheDir'].'/config/',
          'lifeTime' => null,
          'fileNameProtection'=>false,
          'automaticSerialization'=>true
      );
      $optcache = new Cache_Lite($options);
      if(!$moduleopt = $optcache->get($modulename.($userOpt?User::getInstance('office')->getId():'')))  {
    	 	if (@include_once $this->_path.$modulename.'.conf.php')
    	 	{
          if(!is_array($config)) {$config=array();}
   		      $moduleopt = MArray::array_merge_recursive_unique($opt, $config);
    	 	  } else {
    	 	    $moduleopt=$opt;
    	 	  }

        $useropt['all']['tablesToShow'] = Mreg::get('authHelper')->getTablesToShow(User::getInstance('office'));
   		  $moduleopt = MArray::array_merge_recursive_unique($moduleopt, $useropt);        

    	 	$optcache->save($moduleopt);
      }


  	  return $moduleopt;
  }
}