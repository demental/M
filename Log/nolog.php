<?php
class Log_nolog {
  public function message($message,$level)
  {
    return;
  }
  public function __call($meth,$args)
  {
    $message = $arg[0];
    $this->message($message,$meth);
  }
}
