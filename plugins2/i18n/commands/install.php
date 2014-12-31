<?php
class i18n_Command_Install extends Command {
  public static function preSetup()
  {
    // Using T editor driver to avoid script termination on non-existing xml files
    // (so that whatever the order in which the extractlng and the install_i18n scripts ar run, it's ok)
    T::setConfig(array_merge(T::$config,array('driver'=>'editor')));
    return true;
  }
  public function execute($params,$options = array())
  {
    if(count($params)>0) {
      foreach($params as $table) {
        $this->_checkAndInstall($table);
      }
    } else {
      require 'M/DB/DataObject/Advgenerator.php';
      $g = new DB_DataObject_Advgenerator();
      $generators = $g->getGenerators();

      foreach($generators as $agenerator) {
        foreach($agenerator->tables as $table) {
          $this->_checkAndInstall($table);
        }
      }  
    }
  }
  
  protected function _checkAndInstall($table) {
    $t = DB_DataObject::factory($table);
    $tdef = $t->_getPluginsDef();
    if($tdef['i18n']) {
      echo $table.' has i18n fields, generating '.$table.'_i18n table'."\n";
      $t->getPlugin('i18n')->generateTable($t);
    } else {
      $this->line($table.' : no i18n');
    }
  }
}