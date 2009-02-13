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