<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Command.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Class for command-line generation
 */

class Command {
  
  /**
   * Command interface core
   */
  public static function start(){
    self::header('Application '.APP_NAME.' booted'."\n".'Welcome to the M command line tool'."\n\n".'Type \'help\' for full commands list');

    while(1) {
      $readline = popen('history -r "/tmp/.getline_history"
      LINE=""
      read -re -p "M console (Current App : '.APP_NAME.') > " LINE
      history -s "$LINE"
      history -w "/tmp/.getline_history"
      echo $LINE','r');
      $res = ereg_replace(';$','',trim(substr(fgets($readline,1024),0,-1)));
      fclose($readline);
      $args = explode(' ',$res);
      $command = preg_replace('`\W`','',array_shift($args));

      try {
        $exec = self::factory($command);
        $exec->execute($args);
      } catch(Exception $e) {
        self::error($e->getMessage());
      }
    }
  }
  /**
   * Factory to create command instances
   */
  public static function factory($command,$path='M/commands/') {
    $commandfile = $path.strtolower($command).'.php';
    $commandclass = 'Command_'.$command;
    if(!FileUtils::File_exists_incpath($commandfile)) {
      throw new Exception('Command "'.$command.'" not found');
    }
    require_once $commandfile;
    return new $commandclass;
  }
  
  
  /**
   * display a text line (for information)
   * @param string text to be displayed
   */
  public function line($message) {
    echo $message."\n";
  }
  /**
   * Display an inline content
   */
   public function inline($message)
   {
    echo $message;
   }
  /**
   * ask for a yes/no to the CLI user
   * @param string prompt message
   * @param string (default 'n') default value if user just types 'enter'
   * @param string (default 'y') value displayed and expected for positive answer
   * @param string (default 'n') value displayed and expected for negative answer
   */
  public function confirm($message,$default='n',$yes='y',$no='n')
  {
    switch($default) {
      case $yes:
        $yes = strtoupper($yes);
        break;
      default:
        $no = strtoupper($no);
        break;
    }
    self::prompt($message.' ['.$yes.'/'.$no.']');
    $res = strtolower(trim(fgets(STDIN)));
    if(empty($res)) {
      $res = $default;
    }
    return strtolower($res) == strtolower($yes);
  }
  /**
   * Ask the CLI user to choose between several choices
   * @param string prompt message
   * @param string default value if user just types 'enter'
   * @param array indexed array of the different possible choices
   */
  public function choose($message,$default='',$values)
  {
    $message = $message.' ['.implode(' / ',$values).'] (default : '.$default.')';
    $res = self::prompt($message);
    if(empty($res)) {
      $res = $default;
    }
    if(!in_array($res,$values)) {
      self::line('Incorrect input, try again.....');
      self::choose($message,$default,$values);
    } else {
      return $res;
    }
  }
  /**
   * Ask the CLI user to type something, with a default value if provided
   * @param string prompt message
   * @param string default value id user just types 'enter'
   */
  public function ask($message,$default='')
  {
    $message = $message.(empty($default)?'':' ['.$default.']');
    $res = self::prompt($message);
    if(empty($res)) return $default;
    return $res;
  }
  public function error($message)
  {
    echo "\n".'***[ERROR]***'."\n".$message."\n";
  }
  public function info($message)
  {
    echo '[INFO] '.$message."\n";
  }
  public function header($message) {
    echo "\n".str_repeat('*',80)."\n";
    $content = explode("\n",$message);
    foreach($content as $line) {
      printf("* %-76s *\n",$line);
    }
    echo str_repeat('*',80)."\n";  
  }
  public function prompt($message)
  {
    echo "\n".$message.' > ';
    $res = strtolower(trim(fgets(STDIN)));
    return $res;
  }
  /**
   * this method must implement the script fired when command is executed
   * Can throw an Exception if command fails 
   */
  public function execute($params)
  {
    # code...
  }
  
  /**
   * display short help when 'help' global command is fired
   */
  public function shortHelp()
  {
    $this->line('No help for this command');
  }
  
  /**
   * display long help when 'help [command_name]' command is fired
   * @param array additional params that may refer to subcommands
   */  
  public function longHelp($params)
  {
    $this->line('No help for this command');
  }
}