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
* @subpackage   Config
*/
/**
* M PHP Framework
*
* Global configuration helper
* Store and retreive configuration options accross application
*
* @package      M
* @subpackage   Config
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Config {
  protected static $preftab = array();
  protected static $cfgArr = array();
  
  public static function load($array)
  {
    
    self::$cfgArr = array_merge(self::$cfgArr,$array);
  }  
  public static function getAlternateLangs() {
        $l=Config::getAllLangs();
        $cur=array_search(DEFAULT_LANG,$l);
        unset($l[$cur]);
        return $l;
    }
  public static function getAllLangs() {
        return Config::get('installedLangs');
    }
    
  public static function get($var) {
        return self::$cfgArr[$var];
    }
    public static function set($var,$val) {
          self::$cfgArr[$var]=$val;
      }    
  public static function getPref($var) {
	  if(!key_exists($var,self::$preftab)){
  		$res = & DB_DataObject::factory('preferences');
  		$res->var=$var;
  		$res->find(true);
  		if($res->type=='array') {
  		  $temp = explode("\n",$res->val);
        foreach($temp as $k=>$v) {
          $temp2 = explode(':',$v);
          $temp3[trim($temp2[0])] = trim($temp2[1]);
        }
        $res->val = $temp3;
  		}
      self::$preftab[$var]=$res->val;
    }
		return self::$preftab[$var];
	}
	public static function arrayize(&$item,$key)
	{
	 # code...
	}
	public static function setPref($var,$val)
	{
		$res = & DB_DataObject::factory('preferences');
		$res->var=$var;
		if(!$res->find(true)) {
		  return false;
		}
		$res->val = $val;
		$res->update();
		self::$preftab[$var]=$val;
		return self::$preftab[$var];
	}
}