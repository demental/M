<?php
ini_set('memory_limit','512M');
$vars = $_SERVER['argv'];
//var_dump($vars);
$host = $vars[1];
$app_name = $vars[2];
$commandlang = $vars[3];
if(!$host || !$app_name || !$commandlang) {
echo
<<<HEREDOC
Usage : 
php extractlng.php host_name app_name target_language

What it does :
Scan app files, finds all strings called by __ or _e functions and creates or append new strings in lang/target_language.xml file

Limitations :
target language must be one of the langs in Config::getAllLangs()

HEREDOC;
  return;
}
define ('APP_NAME',$app_name);
if(!file_exists('M_Startup.php')) {
  $inc = '../M_Startup.php';
} else {
  $inc = 'M_Startup.php';
}
if(!require $inc) {
  die('Not in M Project');
}
// TODO use getConfig instead of T::$config
T::setConfig(array_merge(T::$config,array('driver'=>'editor')));
T::setLang($commandlang);

$pwd = $_ENV['PWD'];

foreach(FileUtils::getAllFiles($pwd) as $file ) {
  $result = preg_match_all('`(?:__|_e)\(\'(.+)\'(?:,array\(.+\))?\)`sU',file_get_contents($file),$matches);
  foreach($matches[1] as $elem) {
    $nbfound++;
    __(str_replace("\'","'",$elem));
  }
}
$arr = T::getInstance($commandlang)->getStrings();
T::getInstance($commandlang)->save(true);