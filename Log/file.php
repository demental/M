<?php
class Log_file {
  public function init($options)
  {
    $this->_options = $options;
  }
  public function getdump($var)
  {
    $out='';
    switch(true) {
      case is_object($var):
        foreach(get_object_vars($var) as $k=>$v) {
          $out.="\t".$k.' = '.$v."\n";
        }
      break;  
      case is_array($var):
      foreach($var as $k=>$v) {

        $out.="\t".$k.' = '.$this->getdump($v)."\n";
      }
      break;
      default:
        $out.="\t".$k."\n";
    }
    return $out;
  }
  public function dump($var)
  {
    $this->write(print_r($var,true),null,false);
  }
  public function section($title)
  {
    $this->write('########## '.$title.' ##########',null,false);
  }
  public function __call($meth,$args)
  {
    $this->write($args[0],$meth);
  }
  public function write($message,$level,$prepend = true)
  {
    if($prepend) {
      $prefix = date('Y-m-d H:i:s').sprintf('%15s',strtoupper($level)).'::';
    } else {
      $prefix = '';
    }
    file_put_contents($this->_options['file'],$prefix.$message."\n",FILE_APPEND);
  }
}