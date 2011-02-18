<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Command.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Class for command-line generation
 */

class Command {
  /**
   * Options that can be set to true at startup
   * @example : sh>./M host_name.org front --noheader --noninteractive
   */
  protected static $options = array(
    'noheader'=>false,
    'noninteractive'=>false,
    'silent'=>false,
    'assetsurlrewriting'=>false,
  );
  /**
   * Command interface core
   */
  public static function start($initialcommand,$options = array()){

    self::setOptions($options);
    if(!self::getOption('noheader')) {
      self::header('Application '.APP_NAME.' booted'."\n".'Welcome to the M command line tool'."\n\n".'Type \'help\' for full commands list');
    }
    if(!empty($initialcommand)) {
      self::launch($initialcommand);
    }
    while(1 && !self::getOption('noninteractive')) {
      $readline = popen('history -r "/tmp/.getline_history"
      LINE=""
      read -re -p "M console (Current App : '.APP_NAME.') > " LINE
      history -s "$LINE"
      history -w "/tmp/.getline_history"
      echo $LINE','r');
      $res = preg_replace('`;$`','',trim(substr(fgets($readline,1024),0,-1)));
      fclose($readline);
      self::launch($res);
    }
    if(self::getOption('noninteractive'))
    self::launch('exit');
  }
  public static function launch($input) {
    if(strpos($input,'--')===0) {
      $input = explode('=',$input);
      $input[0] = preg_replace('`^--`','',$input[0]);
      if(empty($input[0])) {
        self::error('no option specified');return;
      }
      if(isset($input[1])) {
        self::setOption(trim($input[0]),trim($input[1]));
      } else {
        self::setOption(trim($input[0]),true);
      }
      self::line('Set "'.$input[0].'" option to '.self::getOption($input[0]));
      return;
    }
    $args = explode(' ',$input);
    $command = preg_replace('`\W`','',array_shift($args));
    list($params,$options) = self::parseArgs($args);
    try {
      $exec = self::factory($command);
      $exec->execute($params,$options);
    } catch(Exception $e) {
      self::error($e->getMessage());
    }
  }
  /**
   * Determines from an args array which ones should be considered as arguments and which ones should be considered as options
   * And returns an array of two arrays 
   * @param array raw args list
   * @return array array(array(args),array(options))
   */ 
  public static function parseArgs($arr)
  {
    $options = array();
    $params = array();
    foreach($arr as $item) {
      // option
      if(strpos($item,'--')===0) {
        $item = explode('=',$item);
        $item[0] = preg_replace('`^--`','',$item[0]);
        if(empty($item[0])) {
          // empty option => bypass
          continue;
        }
        if(isset($item[1])) {
          $options[trim($item[0])] = trim($item[1]);
        } else {
          $options[trim($item[0])] = true;
        }
      } else {
        $params[] = $item;
      }
    }
    return array($params,$options);
  }
  public static function setOptions($options) {
    self::$options = array_merge(self::$options,$options);
  }
  public static function setOption($option,$val) {  
    self::$options[$option] = $val;
  }
  public static function getOption($name)
  {
    return self::$options[$name];
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
    if(self::getOption('silent')) return;
    echo $message."\n";
  }
  /**
   * Display an inline content
   */
   public function inline($message)
   {
     if(self::getOption('silent')) return;
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
    if(self::getOption('noninteractive')){
      $res = $default;
      self::line($message.' : '.$res);
    } else {
      $res = self::prompt($message.' ['.$yes.'/'.$no.']');
      if(empty($res)) {
        $res = $default;
      }
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
    if(!self::getOption('noninteractive')){
      $res = self::prompt($message);
    }
    if(empty($res)) {
      self::line($message.' : '.$default);
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
    if(!self::getOption('noninteractive')) {
      $res = self::prompt($message);
    }
    if(empty($res)) return $default;
    return $res;
  }
  public function error($message)
  {
    if(self::getOption('silent')) return;
    echo "\n".'***[ERROR]***'."\n".$message."\n";
  }
  public function info($message)
  {
    if(self::getOption('silent')) return;    
    echo '[INFO] '.$message."\n";
  }
  public function header($message) {
    if(self::getOption('silent')) return;    
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
  public function execute($params,$options)
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