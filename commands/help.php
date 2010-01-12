<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   help.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Command to display help
 */
class Command_Help extends Command {

  public function shortHelp()
  {
    $this->line('Displays this help');
  }
  public function execute($params)
  {
    if(is_array($params) && count($params)>0) {
      $command = array_shift($params);
      $exec = Command::factory($command);
      $exec->longHelp($params);
    } else {
      $dir = dirname(realpath(__FILE__));
      foreach(FileUtils::getAllFiles($dir) as $afile) {
        $subcname = basename($afile,'.php');
        $subc = Command::factory($subcname);
        $this->line('====== '.$subcname.' ======');
        $subc->shortHelp();
      }
    }
  }
}