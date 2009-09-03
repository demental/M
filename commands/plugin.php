<?php

$vars = $_SERVER['argv'];
//var_dump($vars);
$plugin = strtolower($vars[1]);
$command = strtolower($vars[2]);
$host = strtolower($vars[3]);
$app = strtolower($vars[4]);

if(!$host || !$command || !$host || !$app) {
echo 'Usage : ';
echo 'php plugin.php plugin_name command_name host_name app_name';
echo '';
echo 'What it does :';
echo 'Launches a plugin command (if exists)';
echo '';

  return;
}

define ('APP_NAME',$app);
if(!file_exists('M_Startup.php')) {
  $inc = '../M_Startup.php';
} else {
  $inc = 'M_Startup.php';
}
if(!require $inc) {
  die('Not in M Project');
}

// Trying to include the plugin command and fire preSetup

if(!FileUtils::file_exists_incpath ('M/Plugins/'.$plugin.'/commands/'.$command.'.php')) {
  if(!FileUtils::file_exists_incpath ('M/Plugins/'.$plugin)) {
    die($plugin.' plugin does not exist');
  } else {
    die($plugin.' does not have '.$command.' command');
  }
}
require 'M/Plugins/'.$plugin.'/commands/'.$command.'.php';
$className = $plugin.'_Command_'.$command;
eval('$result = '.$className.'::preSetup();');
if($result === false) {
  die($command.' could not be fired');
}
// Setting up options including Databases DSN
Mreg::get('setup')->setUpEnv();

// Executing
eval('$result = '.$className.'::execute();');
if($result === false) {
  echo "\Failed\n";
} else {
  echo "\Done\n";
}