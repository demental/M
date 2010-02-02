<?php
class Command_reboot extends Command {
  public function execute($params)
  {
    $this->line('Rebooting.....');
    $str  = $GLOBALS['argv'][0].' '.$GLOBALS['argv'][1].' '.$GLOBALS['argv'][2].' '.implode(' ',$params);  
    die(passthru($str));
  }
}