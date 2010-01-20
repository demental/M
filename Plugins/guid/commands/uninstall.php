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
  protected $toScan = array();

  public function longHelp($params)
  {
    $this->line('This command transforms CHAR(36) primary keys of the provided tables into numeric, auto-increment values.');
    $this->line('* All foreign records related to the provided tables are also updated, including dynamic/conditional links');
    $this->line('* The guid plugin is then removed from the DOclass definition');
    $this->line('DISCLAIMER:');
    $this->line('* This command cannot be reverted');
    $this->line('* The execution time can be very long, if your database contains several tables with dynamic/conditional links,');
    $this->line('as well as for tables with no index on the foreign key(s) that will be updated');        
    $this->line('');
    $this->line('Usage:');
    $this->line('plugin guid uninstall [TABLE1] [TABLE2].....[TABLEN]');
  }

  protected function _checkForCustomLinks()
  {
    $data = PEAR::getStaticProperty('DB_DataObject', 'options');
    $folder = $data['class_location'];
    $files = FileUtils::getAllFiles($folder,'php');
    foreach($files as $file) {
      $content = file_get_contents($file);
      $classname = strtolower(basename($file,'.php'));
      if(DB_DataObject_Advgenerator::hasCustomLinksMethod($content)) {
        if($this->confirm($classname.' class seems to have dynamic/conditional links.'."\n".'Do you wish to scan all table for link fix (long but recommended) ?','y')) {
          $this->toScan[]=$classname;
        }
      }
    }
  }
  public function execute($params)
  {
    if(count($params)>0) {
      $this->_checkForCustomLinks();
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
       $this->_checkForCustomLinks();
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
       $data = ereg_replace('(\'|")guid(\'|")[[:space:]]*=>[[:space:]]*(true|1),*','',$data);
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
       if($linkField!=$d->pkName()) continue;
       $this->line('Updating values for '.$ftable);
       if(in_array($ftablearr[0],$this->toScan)) {
         $this->line($ftablearr[0].' has dynamic/conditional links... IGNORING');
         continue;
       }
       $this->toRegenerate[] = $ftablearr[0];       
       $q = 'UPDATE %1$s t1,%2$s t2 SET t2.%3$s=t1.n_id where %3$s=t1.%4$s';
       $db->query(vsprintf($q,array($table,$ftablearr[0],$ftablearr[1],$linkField)));
       $q2 = 'ALTER TABLE %s CHANGE `%s` `%s` %s UNSIGNED NOT NULL';
       $query2 = vsprintf($q2,array($ftablearr[0],$ftablearr[1],$ftablearr[1],$fieldSize));
       $this->line('Altering field '.$ftable.' to '.$fieldSize);
       $db->query($query2);
     }
    foreach($this->toScan as $tableToScan) {
      $this->line('Scanning all records from '.$tableToScan);
      $rec = DB_DataObject::factory($tableToScan);
      $rec->find();
      while($rec->fetch()) {
        $links = $rec->links();
        foreach($links as $field=>$ftabledata) {
          $ftablearr = explode(':',$ftabledata);
          $ftable = $ftablearr[0];
          $ffield = $ftablearr[1];
          if($ftable == $table && $ffield == $d->pkName()) {
            $this->inline('.');
            $q = 'UPDATE %s SET %s=(SELECT n_id FROM %s WHERE %s=%s) WHERE %s=%s';
            $fq = vsprintf($q,
              array($db->quoteIdentifier($tableToScan),
                    $db->quoteIdentifier($field),
                    $db->quoteIdentifier($table),
                    $db->quoteIdentifier($d->pkName()),
                    $db->quote($rec->{$field}),
                    $db->quoteIdentifier($rec->pkName()),
                    $db->quote($rec->pk())
                    ));
            $db->exec($fq);
          }
        }
      }
    }
    // Third loop : we remove the old 'id' field and rename 'n_id' to 'id'
      $this->line('Dropping old guid pk.');
      $db->query('ALTER TABLE `'.$d->tableName().'` DROP id');
      $db->query('ALTER TABLE `'.$d->tableName().'` CHANGE `n_id` `id` '.$fieldSize.' UNSIGNED NOT NULL AUTO_INCREMENT');
  }
}