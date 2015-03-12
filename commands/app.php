<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   app.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Command to invoque an application-specific command.
 * i.e. the command file resides in APP_ROOT.'app/'.APP_NAME.'/commands/'
 */

class Command_App extends Command {
  public function shortHelp()
  {
    $this->line('Invoques an app-specific subcommand');
  }
  public function longHelp($params)
  {
    if(count($params)==0) {
      $this->line('Invoques an app-specific subcommand');
      $this->line('Usage:');
      $this->line('app [COMMAND_NAME] [[COMMAND_PARAMETERS]]');
      $this->line('');
      $this->line('You can also get help from a subcommand if provided using:');
      $this->line('help app [COMMAND_NAME]');
      $this->line('============');
      $this->line('Here is a list of available app-specific commands:');
      $commandsPath = APP_ROOT.'app/'.APP_NAME.'/commands/';
      foreach(FileUtils::getFiles($commandsPath,'php') as $afile) {
        $commandName = strtolower(basename($afile,'.php'));
        $command = self::factory($commandName);
        $this->line("\t".$commandName);
        $command->shortHelp();
      }
    } else {
      $commandName = array_shift($params);
      self::factory($commandName)->longHelp($params);
    }
  }
  public static function factory($command,$path='M/commands/') {
      $path = APP_ROOT.'app/'.APP_NAME.'/commands/';
    return parent::factory($command,$path);
  }

  public function execute($params, $options)
  {
    $command = array_shift($params);
    $exec = self::factory($command);
    $exec->execute($params, $options);
  }
}
