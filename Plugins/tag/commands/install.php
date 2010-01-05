<?php
/**
 * Installing necessary data for the tag plugin to work :
 *  - 3 tables
 *  - 3 DataObjects into the project's DB folder
 * @todo create a base class for plugin installers providing useful methods :
 *  - Retreiving database connection
 *  - Clearing config cache
 *  - Getting information about the project (mostly paths), while we use here the constant DB_FOLDER
 * it might be a better idea to have methods instead of constants ?
 */

class Tag_Command_Install implements iCommand {
  public static function preSetup()
  {
      
  }
  public static function execute()
  {
    $db = MDB2::factory(M::getDatabaseDSN());
    $h = $db->dsn['hostspec'];
    $u = $db->dsn['username'];
    $p = $db->dsn['password'];
    $dbn = $db->database_name;
    $file = M::getPearpath().'/M/Plugins/tag/src/tag.sql';
    $mysqlbin = M::getSQLbinpath();
    $sys = "cat $file | $mysqlbin --host=$h --user=$u --password=$p $dbn";
    system($sys,$return);
    Log::info('Creating tables :
* tag
* tag_record
* tag_history
');
    foreach(array('Tag','Tag_record','Tag_history') as $table) {
      Log::info('Creating DOclass : '.$table."\n");
      copy(M::getPearpath().'/M/Plugins/tag/src/'.$table.'.php',APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'DOclasses'.DIRECTORY_SEPARATOR.$table.'.php');
    }
  }
}