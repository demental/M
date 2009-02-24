<?php
$vars = $_SERVER['argv'];
//var_dump($vars);
$host = $vars[1];
$app_name = $vars[2];
define ('APP_NAME',$app_name);
if(!file_exists('M_Startup.php')) {
  $inc = '../M_Startup.php';
} else {
  $inc = 'M_Startup.php';
}
if(!require $inc) {
  die('Not in M Project');
}


$pwd = $_ENV['PWD'];

foreach(FileUtils::getAllFiles($pwd) as $file ) {
  $result = preg_match('`__\(\'(.*)\)`sU',file_get_contents($file),$matches);
  array_shift($matches);
  foreach($matches as $elem) {
    $langs[$elem] = $elem;
  }
}
var_dump($langs);