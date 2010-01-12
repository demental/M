<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   plugin.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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
      $this->line('You can also get help from subcommands if provided using:');
      $this->line('help plugin [PLUGIN_NAME] [COMMAND_NAME]');
    }
  }
  public function execute($params)
  {
    if(!$params[0] && !$params[1]) {
      throw new Exception('You must type at least plugin name and command name.'."\n".'Type \'help plugin\' for more information');
    }
    $plugin = array_shift($params);
    $command = array_shift($params);    

    if(!FileUtils::file_exists_incpath ('M/Plugins/'.$plugin.'/commands/'.$command.'.php')) {
      if(!FileUtils::file_exists_incpath ('M/Plugins/'.$plugin)) {
        throw new Exception($plugin.' plugin does not exist');
      } else {
        throw new Exception($plugin.' does not have '.$command.' command');
      }
    }
    require_once 'M/Plugins/'.$plugin.'/commands/'.$command.'.php';
    $className = $plugin.'_Command_'.$command;
    // Setting up options including Databases DSN
    Mreg::get('setup')->setUpEnv();

    // Executing
    $com = new $className;
    $result = $com->execute($params);
    if($result === false) {
      throw new Exception("\Failed");
    } else {
      echo 'Done';
    }
  }
}