<?php
// ==========================================================
// = Url generation class. Includes "old" and "new" flavour
// = the new flavour uses PEAR Net_URL_Mapper and is more powerful. But not used in production yet...
// = "old" flavour simply creates a module/action then appends the rest as a query string
// ==========================================================
class URL {
  public static $method='old';
  public static $bases;
  public static function setBases($bases) {
    self::$bases = $bases;
  }
  public static function getBases()
  {
    return self::$bases;
  }
    public static function e($route,$params = null,$lang=null,$isSecure=null)
    {
        echo self::get($route,$params,$lang,$isSecure);
    }
    public static function ea($route,$params = null,$lang=null)
    {
        echo self::getAnchor($route,$params,$lang);
    }
    public static function get($route,$params = null,$lang=null,$isSecure=null)
    {
      if($isSecure===true) {
        $url = self::$bases['secure'];
      } elseif($isSecure===false) {
        $url = self::$bases['unsecure'];
      } else {  
        $url = self::$bases[0];
      }
        return $url.self::getAnchor($route,$params,$lang);
    }

    public static function getAnchor($route,$params = null,$lang=null)
    {
      if(self::$method=='old') {
        return self::getOldAnchor($route,$params,$lang);
      }
          unset($params['lang']);
          unset($params['module']);
          unset($params['action']);          

          if($lang==null) {
            $lang=T::getLang();
          }
          $params['lang'] = $lang;
          list($params['module'],$params['action']) = explode('/',$route);
          if(empty($params['action'])) {$params['action'] = null;}
          return Net_URL_Mapper::getInstance()->generate($params);
    }
    public static function getOldAnchor($route,$params = null,$lang=null)
    {
      
          unset($params['lang']);
          unset($params['module']);
          unset($params['action']);          

          if($lang==null) {
            $lang=T::getLang();
          }
          if($params == null) {
            return $lang.'/'.$route;
          }
          return $lang.'/'.$route.'?'.http_build_query($params);
    }
    public static function getFor($lang)
    {
      $g = $_GET;
      $route = $g['module'].($g['action']?'/'.$g['action']:'');
      unset($g['module']);
      unset($g['action']);
      return self::get($route,$g,$lang);
    }
    public static function getself()
    {
      return self::get($_GET['module'].'/'.$_GET['action'],$_GET);
    }
    public static function eself()
    {
      echo URL::getself();
    }
}