<?php
$vars = $_SERVER['argv'];
//var_dump($vars);
$host = $vars[1];
$app_name = $vars[2];
$lang = $vars[3];
if(!$host || !$app_name || !$lang) {
echo
<<<HEREDOC
Usage : 
php install_i18n.php host_name

What it does :
Creates i18n tables for DOclasses that have i18n fields, to be done once only (hence already created i18n tables won't be overwritten)

Requirements :
You need to have already generated model objects and configured it to have i18n fields 
To do so add a i18nfields public property to your model object :
public $i18nfields = array('field1','field2'....));

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
