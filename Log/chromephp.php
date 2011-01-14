<?php
require_once 'M/lib/chromephp/ChromePhp.php';

ChromePhp::useFile(LOG_DRIVER_FILE, LOG_DRIVER_URL);


class Log_chromephp {
  public function message($message,$level)
  {
    switch ($level)
    {
      case 'error' :
        ChromePhp::error($message);
      break;
      case 'warning' :
        ChromePhp::warning($message);
      break;
      case 'info' :
      default :
        ChromePhp::log($message);
      break;
    }
  }
  public function table($caption,$data)
  {
    ChromePhp::log($caption,$data);
  }  
}