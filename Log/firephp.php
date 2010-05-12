<?php
require_once 'M/lib/firephp/FirePHP.class.php';

class Log_firephp {
  public function message($message,$level)
  {
    $fp = FirePHP::getInstance(true);
    if(!in_array($level,array('log','info','warn','error'))) {
      $level = 'log';
    }
    call_user_func_array(array($fp,$level),array($message));  
  }
  public function table($caption,$data)
  {
        $fp = FirePHP::getInstance(true);
        $fp->table($caption,$data);
  }
}