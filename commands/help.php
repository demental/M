<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   help.php
 * @author       Arnaud Sellenet <demental at github>
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
      $this->line('This command displays global help text or specific help text for a command if provided');
      $this->line('Usage:');
      $this->line('help');
      $this->line('  Displays this help');
      $this->line('help [COMMAND_NAME]');
      $this->line('  Displays specific and longer help for [COMMAND_NAME]');
      $this->line('help [COMMAND_NAME] [SUBCOMMAND_NAME] and so on....');
      $this->line('  Displays specific and longer help for [SUBCOMMAND_NAME] if [COMMAND_NAME] contains some subcommands (e.g. the plugin command)');
      $this->line('==========');
      $this->line('Here is the list of available root commands:');
      $dir = dirname(realpath(__FILE__));
      foreach(FileUtils::getAllFiles($dir, 'php') as $afile) {
        $subcname = basename($afile,'.php');
        $subc = Command::factory($subcname);
        $this->line('====== '.$subcname.' ======');
        $subc->shortHelp();
      }
    }
  }
}
