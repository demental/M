<?php

class T_reader extends T {
  public function translate( $string, $args = null )
  {
    if(empty($string)) {
      return;
    }
      if(!key_exists($string,$this->strings)) {
        return $args?vsprintf($string,$args):$string;
      }
      $string = $this->strings[$string];
      if(is_array($args)) {
        return vsprintf($string,$args);
      }
      return $string;
  }
}