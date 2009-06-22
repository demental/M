<?php
/**
 * This command will uninstall officepack from all tables that use this plugin :
 * - removes PRIMARY KEY attribute from the ID field
 * - adds a new field (n_id) and declares it as PRIMARY KEY AUTO_INCREMENT
 * - scans all tables and checks for links() for each of them. If the link uses officepack, 
 * changes the current foreign key value to the new value n_id
 * - removes the id field and renames the n_id field to 'id'
 * - regenerates models
 * - removes officepack plugin from all the DOs.
 * - if a 'deleted' field exists, adds falsedelete plugin.
 */

class officepack_command_uninstall implements iCommand {

  public static function preSetup()
  {
    return;
  } 
  public static function execute()
  {
     require 'M/DB/DataObject/Advgenerator.php';
     $g = new DB_DataObject_Advgenerator();
     $generators = $g->getGenerators();
     $officepacktables = array();
     // First loop : creating new primary key.
     foreach($generators as $agenerator) {
       foreach($agenerator->tables as $table) {
         echo 'Checking officepack value for '.$table."\n";

         $d = DB_DataObject::factory($table);
         $db = $d->getDatabaseConnection();
         $defs = $d->_getPluginsDef();
         if($d->officePack || $d->officepack || key_exists('officepack',$defs)) {
            $officepacktables[]=$d->tableName();
           echo $table.' has officepack plugin. Adding numeric primary key'."\n";
           // Removing current primary key and adding a new numeric primary key.
           $req = 'ALTER TABLE `'.$d->tableName().'` DROP PRIMARY KEY';
           echo $req."\n";
           $res = $db->query($req);
           if(PEAR::isError($res)) {
             die($res->getMessage());
           }
           $res = $db->query('ALTER TABLE `'.$d->tableName().'` ADD `n_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;');
           if(PEAR::isError($res)) {
             die($res->getMessage());
           }
           // Scanning all reverselink to use new pk value.
         }
       }
     }
    // Second loop : individually (per-record) correct links
    foreach($generators as $agenerator) {
      foreach($agenerator->tables as $table) {
        $d = DB_DataObject::factory($table);
        $d->unloadPlugin('officepack');
        $d->find();
        while($d->fetch()) {
          echo "\n";
          foreach($d->links() as $field=>$tinfo) {
            // Non numeric & non empty field => we assume it's a GUID field
            if(!is_numeric($d->$field) && !empty($d->$field)) {
              $tableinfo = explode(':',$tinfo);
              $req = 'SELECT n_id FROM '.$db->quoteIdentifier($tableinfo[0]).' WHERE '.$db->quoteIdentifier($tableinfo[1]).'='.$db->quote($d->$field);
              echo ".";
              $newid = $db->queryOne($req);
              if(pear::isError($newid)) {
                die($newid->getMessage());
              }
              $req= 'UPDATE '.$db->quoteIdentifier($d->tableName()).' SET '.$db->quoteIdentifier($field).' = '.$db->quote($newid).' WHERE id='.$db->quote($d->id);
//              echo $req."\n";
              $res = $db->query($req);
              if(pear::isError($res)) {
                die($res->getMessage());
              }
              
            }
          }
        }
      }
    }
    // Third loop : we remove the old 'id' field and rename 'n_id' to 'id'
    // Also we replace officepack with falsedelete if a 'deleted' field exists
    foreach($officepacktables as $table) {
      $db->query('ALTER TABLE `'.$d->tableName().'` DROP id');
      $db->query('ALTER TABLE `'.$d->tableName().'` CHANGE `n_id` `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT');
    }
  }
}