<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   sh.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
* command to launch shell commands
*/
class Command_Sh extends Command
{
  public function shortHelp()
  {
    $this->line('launches a shell command');
  }
  public function longHelp($params)
  {
    $this->line('Launches a shell command');
    $this->line('Usage:');
    $this->line('sh command_to_launch');
  }
  public function execute($params)
  {
    if(count($params) == 0) {
      return $this->error('No shell command specified');
    }
    $params = implode(' ',$params);
    $this->line('Launching shell command:'.$params);
    passthru($params);
  }
}
