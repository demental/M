<?php
class i18n_Command_Install implements iCommand {
  public static function preSetup()
  {
    // Using T editor driver to avoid script termination on non-existing xml files
    // (so that whatever the order in which the extractlng and the install_i18n scripts ar run, it's ok)
    T::setConfig(array_merge(T::$config,array('driver'=>'editor')));
    return true;
  }
  public static function execute()
  {
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
    return true;
  }
}