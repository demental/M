<?php

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