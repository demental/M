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
require 'M/DB/DataObject/Advgenerator.php';
class Guid_command_uninstall extends Command {
  
  protected $toRegenerate = array();
  protected $toRemove = array();

  public function execute($params)
  {


    if(count($params)>0) {
      foreach($params as $table) {
        $do = DB_DataObject::factory($table);
        $defs = $do->_getPluginsDef();
        if(key_exists('guid',$defs)) {
          $this->_removeGuidFromTable($table);
        } else {
          $this->line($table.' dont have guid installed. IGNORED');
        }
      }
    } else {
      if(!$this->confirm('You are about to uninstall guid on the whole project! ARE YOU SURE ?')) {echo 'Aborting';return;}
       $g = new DB_DataObject_Advgenerator();
       $generators = $g->getGenerators();
       $officepacktables = array();
       // First loop : creating new primary key.
       foreach($generators as $agenerator) {
         foreach($agenerator->tables as $table) {
           $this->line('Checking guid value for '.$table);

           $d = DB_DataObject::factory($table);
           $defs = $d->_getPluginsDef();
           if(key_exists('guid',$defs)) {
             $this->line($table.' has guid plugin. Uninstalling it....');
             $this->_removeGuidFromTable($table);           
           }   
         }
       }
     }
     // Regenerating modified tables
/*     $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
     $oldoption = $options['generator_include_regex'];
     $options['generator_include_regex'] = '`^('.explode('|',$this->toRegenerate).')$`';*/
     $this->line('regenerating DOclasses');
 	   $generator = new DB_DataObject_Advgenerator();
 	   $generator->start();
/*     $options['generator_include_regex'] = $oldoption;*/
     // Removing guid plugin to the DO file
     foreach($this->toRemove as $table) {
       $this->line('removing plugin guid for table '.$table);
       $data = file_get_contents(APP_ROOT.PROJECT_NAME.'/DOclasses/'.ucfirst($table).'.php');
       $data = ereg_replace('(\'|")guid(\'|")[[:space:]]*=>[[:space:]]*(true|1),','',$data);
       file_put_contents(APP_ROOT.PROJECT_NAME.'/DOclasses/'.ucfirst($table).'.php',$data);
     }
     
   }
   protected function _removeGuidFromTable($table)
   {
     $this->toRegenerate[] = $table;
     $this->toRemove[] = $table;
     $d = DB_DataObject::factory($table);
     $db = $d->getDatabaseConnection();
     $fieldSize = $this->choose('Choose the primary key type for '.$table,'INT(11)',array('INT(11)','BIGINT(20)'));
     // Removing current primary key and adding a new numeric primary key.
     $req = 'ALTER TABLE `'.$d->tableName().'` DROP PRIMARY KEY';
     $res = $db->query($req);
     if(PEAR::isError($res)) {
       die($res->getMessage());
     }
     $res = $db->query('ALTER TABLE `'.$d->tableName().'` ADD `n_id` '.$fieldSize.' UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;');
     if(PEAR::isError($res)) {
       die($res->getMessage());
     }
     foreach($d->reverseLinks() as $ftable=>$linkField) {
       $ftablearr = explode(':',$ftable);
       $this->line('Updating values for '.$ftable);
       $this->toRegenerate[] = $ftablearr[0];       
       $q = 'UPDATE %1$s,%2$s SET %2$s.%3$s=%1$s.n_id where %3$s=%1$s.%4$s';
       $db->query(vsprintf($q,array($table,$ftablearr[0],$ftablearr[1],$linkField)));
       $q2 = 'ALTER TABLE %s CHANGE `%s` `%s` %s UNSIGNED NOT NULL';
       $query2 = vsprintf($q2,array($ftablearr[0],$ftablearr[1],$ftablearr[1],$fieldSize));
       $this->line('Altering field '.$ftable.' to '.$fieldSize);
       $db->query($query2);
     }
    // Third loop : we remove the old 'id' field and rename 'n_id' to 'id'
      $this->line('Dropping old guid pk.');
      $db->query('ALTER TABLE `'.$d->tableName().'` DROP id');
      $db->query('ALTER TABLE `'.$d->tableName().'` CHANGE `n_id` `id` '.$fieldSize.' UNSIGNED NOT NULL AUTO_INCREMENT');
  }
}