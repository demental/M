<?php

class T_editor extends T {
  public static $curStrings;
  public function translate( $string, $args = null )
  {
    if(empty($string)) {
      return;
    }
    self::$curStrings[]=$string;
      if(!key_exists($string,$this->strings)) {
        return vsprintf($string,$args);
      }
      $string = $this->strings[$string];
      if(is_array($args)) {
        return vsprintf($string,$args);
      }
      return $string;
  }
  public static function getCurStrings()
  {
    return array_unique(self::$curStrings);
  }
}