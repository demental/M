<?php
$vars = $_SERVER['argv'];
//var_dump($vars);
$host = $vars[1];
if(!$host) {
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
define ('APP_NAME','whatever');
if(!file_exists('M_Startup.php')) {
  $inc = '../M_Startup.php';
} else {
  $inc = 'M_Startup.php';
}
if(!require $inc) {
  die('Not in M Project');
}
// Using T editor driver to avoid script termination on non-existing xml files
// (so that whatever the order in which the extractlng and the install_i18n scripts ar run, it's ok)
T::setConfig(array_merge(T::$config,array('driver'=>'editor')));

// Setting up options including Databases DSN
Mreg::get('setup')->setUpEnv();


require 'M/DB/DataObject/Advgenerator.php';
$g = new DB_DataObject_Advgenerator();
$generators = $g->getGenerators();

foreach($generators as $agenerator) {
  foreach($agenerator->tables as $table) {
    echo 'Checking i18n value for '.$table."\n";

    $d = DB_DataObject::factory($table);
    if(isset($d->i18nFields)) {
      echo $table.' has i18n fields, generating '.$table.'_i18n table'."\n";

      $d->_loadPlugins();
      $d->getPlugin('international')->generateTable($d);
    } else {
      echo 'not an i18n capable table'."\n";
    }
  }
}

echo "\nDone\n";
