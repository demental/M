<?php
class Payment_Response {
  
  public function __toString()
  {
    $out='<ul>';
    foreach(get_object_vars($this) as $k=>$v) {
      $out.='<li><strong>'.$k.'</strong> : '.$v.'</li>';
    }
    $out.='<ul>';
    return $out;
  }
  public function toArray()
  {
    $out = array();
    foreach(get_object_vars($this) as $k=>$v) {
      $out[$k] = $v;
    }
    return $out;
  }
  public function fromArray($arr)
  {
    foreach($arr as $k=>$v) {
      $this->$k = $v;
    }
  }
}