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
* @subpackage   Maman
*/
/**
* M PHP Framework
*
* Abstract class from which several other classes (like Module or Mail) extend
* Provides internal configuration methods

*
* @package      M
* @subpackage   Maman
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

abstract class Maman
{
    protected $config;
    
    function setConfig($conf)
    {
  	$this->config = $conf;
    }
    function setConfigValue($key,$value, $action = 'all') 
    {
    	$this->config[$action][$key] = $value;
    }
    function getAllConfig() {
        return $this->config;
    }
    function getConfig($value,$action = 'all',$default = false)
    {
      switch(true) {
        case isset($this->config[$action][$value]):
  	      return $this->config[$action][$value];
        case isset($this->config['all'][$value]):
          return $this->config['all'][$value];
        default:
          return false;
      }
    }
}