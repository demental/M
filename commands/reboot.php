<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   reboot.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * reboot the cli
 */
class Command_reboot extends Command {
  public function shortHelp()
  {
    $this->line('Restarts the current command-line interface');
  }
  public function longHelp($params)
  {
    $this->line('Restarts the current command-line interface');
    $this->line('Usage:');
    $this->line('reboot [next_command]');
    $this->line('You can specify a command that will be launched after reboot');
  }
  public function execute($params)
  {
    $this->line('Rebooting.....');
    $str  = $GLOBALS['argv'][0].' '.$GLOBALS['argv'][1].' '.$GLOBALS['argv'][2].' '.implode(' ',$params);  
    die(passthru($str));
  }
}