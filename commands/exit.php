<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   php.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * php interpretor
 */

class Command_exit extends Command {
  public function shortHelp()
  {
    $this->line('Exits the M console.');
  }
  public function longHelp($params)
  {
    $this->line('Exits the M console. No parameters are eligible for this command');
  }
  public function execute($params, $options = array())
  {
    echo 'Bye.'."\n";exit;
  }
}
