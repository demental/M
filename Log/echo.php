<?php
class Log_echo  {
  public $format = '<strong>%s</strong> :: %s<br />';
  public function logMessage($message,$level)
  {
    printf($this->format,$level,$message);
  }
}