<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   plugin.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * command to launch module subcommands
 */

class Command_module extends Command {

  public function shortHelp()
  {
    $this->line('Invoques a module subcommand');
  }
  public function longHelp($params)
  {
    if(count($params)==0) {
      $this->line('Invoques a module subcommand');
      $this->line('Usage:');
      $this->line('module [MODULE_NAME] [COMMAND_NAME] [[COMMAND_PARAMETERS]]');
      $this->line('');
      $this->line('You can get a description of the module and list the available subcommands using:');
      $this->line('help module [MODULE_NAME]');
      $this->line('You can also get help from subcommands if provided using:');
      $this->line('help module [MODULE_NAME] [COMMAND_NAME]');
      $this->line('============');

    } elseif(count($params)==1) {
      $module = $params[0];
      $modulePath = APP_ROOT.'app/'.APP_NAME.'/modules/'.$module.'/';

      if(!file_exists($modulePath.'commands/help.php')) {
        if(!file_exists($modulePath)) {
          throw new Exception($module.' module does not exist while processing help');
        } else {
          $this->line('no description for '.$module.' module');
        }
      } else {
        require_once $modulePath.'/commands/help.php';
        $className = $module.'_Command_help';
        $h = new $className;
        $h->execute();
      }
      $this->line('============');
      $this->line('Here is a list of available commands for this module:');
      $commandsPath = realpath($modulePath.'commands/');
      foreach(FileUtils::getAllFiles($commandsPath) as $file) {
        $commandname = basename($file,'.php');
        if($commandname=='help') continue;
        $className = $module.'_Command_'.$commandname;
        require_once $file;
        $newcommand = new $className();
        $this->line('');
        $this->line($commandname);
        $newcommand->shortHelp();
      }
    } else {
      $module = array_shift($params);
      $command = array_shift($params);
      $this->getModuleCommand($module,$command,$params)->longHelp();
    }
  }
  public function execute($params)
  {
    if(!$params[0] && !$params[1]) {
      throw new Exception('You must type at least module name and command name.'."\n".'Type \'help module\' for more information');
    }
    $module = array_shift($params);
    $command = array_shift($params);

    $com = $this->getModuleCommand($module,$command,$params);
    $result = $com->execute($params);
    if($result === false) {
      throw new Exception("\Failed");
    } else {
      $this->line('Done');
    }
  }
  protected function getModuleCommand($module,$command,$params = array())
  {
    $modulePath = APP_ROOT.'app/'.APP_NAME.'/modules/'.$module.'/';

    if(!file_exists ($modulePath.'/commands/'.$command.'.php')) {
      if(file_exists ($modulePath)) {
        throw new Exception($module.' module does not exist');
      } else {
        throw new Exception($module.' does not have '.$command.' command');
      }
    }
    require_once $modulePath.'/commands/'.$command.'.php';
    $className = $module.'_Command_'.$command;
    // Setting up options including Databases DSN
    Mreg::get('setup')->setUpEnv();

    // Executing
    $com = new $className;
    return $com;
  }
}
