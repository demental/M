<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   php.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * php interpretor
 */

class Command_php extends Command {

  public function shortHelp()
  {
    $this->line('Allows to execute php instructions');
  }
  public function longHelp($params)
  {
    $this->line('Allows to execute php instructions');
    $this->line('Usage:');
    $this->line('php [COMMAND_TO_EXECUTE]');
    $this->line('');
    $this->line('NOTE: you don\'t need the trailing ; at the end of line');
    $this->line('EXAMPLE: php echo "blah"');
  }
  public function execute($params)
  {
    $code = implode(' ',$params).';';
    eval($code);
  }  
}
 