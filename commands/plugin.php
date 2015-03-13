<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   plugin.php
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * command to launch plugin subcommands
 */

class Command_plugin extends Command {

  public function shortHelp()
  {
    $this->line('Invoques a plugin subcommand');
  }
  public function longHelp($params)
  {
    if(count($params)==0) {
      $this->line('Invoques a plugin subcommand');
      $this->line('Usage:');
      $this->line('plugin [PLUGIN_NAME] [COMMAND_NAME] [[COMMAND_PARAMETERS]]');
      $this->line('');
      $this->line('You can get a description of the plugin and list the available subcommands using:');
      $this->line('help plugin [PLUGIN_NAME]');
      $this->line('You can also get help from subcommands if provided using:');
      $this->line('help plugin [PLUGIN_NAME] [COMMAND_NAME]');
      $this->line('============');
      $this->line('Here is a list of available plugins:');
      $dir = dirname(realpath(__FILE__));
      $pluginsPath = realpath($dir.'/../plugins/');
      foreach(FileUtils::getFolders($pluginsPath) as $adir) {
        if(ereg('^\.',basename($adir))) continue;
        $this->line(basename($adir));
      }

    } elseif(count($params)==1) {
      $plugin = $params[0];
      $pluginPath = 'M/plugins/'.$plugin.'/';
      if(!FileUtils::file_exists_incpath ($pluginPath.'commands/help.php')) {
        if(!FileUtils::file_exists_incpath ('M/plugins/'.$plugin)) {
          throw new Exception($plugin.' plugin does not exist');
        } else {
          $this->line('no description for '.$plugin.' plugin');
        }
      } else {
        require_once 'M/plugins/'.$plugin.'/commands/help.php';
        $className = $plugin.'_Command_help';
        $h = new $className;
        $h->execute();
      }
      $this->line('============');
      $this->line('Here is a list of available commands for this plugin:');
      $dir = dirname(realpath(__FILE__));
      $commandsPath = realpath($dir.'/../../'.$pluginPath.'commands/');
      foreach(FileUtils::getAllFiles($commandsPath) as $file) {
        $commandname = basename($file,'.php');
        if($commandname=='help') continue;
        $className = $plugin.'_Command_'.$commandname;
        require_once $file;
        $newcommand = new $className();
        $this->line('');
        $this->line($commandname);
        $newcommand->shortHelp();
      }
    } else {
      $plugin = array_shift($params);
      $command = array_shift($params);
      $this->getPluginCommand($plugin,$command,$params)->longHelp();
    }
  }
  public function execute($params)
  {
    if(!$params[0] && !$params[1]) {
      throw new Exception('You must type at least plugin name and command name.'."\n".'Type \'help plugin\' for more information');
    }
    $plugin = array_shift($params);
    $command = array_shift($params);

    $com = $this->getPluginCommand($plugin,$command,$params);
    $result = $com->execute($params);
    if($result === false) {
      throw new Exception("\Failed");
    } else {
      $this->line('Done');
    }
  }
  protected function getPluginCommand($plugin,$command,$params = array())
  {
    if(!FileUtils::file_exists_incpath ('M/plugins/'.$plugin.'/commands/'.$command.'.php')) {
      if(!FileUtils::file_exists_incpath ('M/plugins/'.$plugin)) {
        throw new Exception($plugin.' plugin does not exist');
      } else {
        throw new Exception($plugin.' does not have '.$command.' command');
      }
    }
    require_once 'M/plugins/'.$plugin.'/commands/'.$command.'.php';
    $className = $plugin.'_Command_'.$command;
    // Setting up options including Databases DSN
    Mreg::get('setup')->setUpEnv();

    // Executing
    $com = new $className;
    return $com;
  }
}
