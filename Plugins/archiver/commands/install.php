<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   install.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Installs the required field for archiver plugin
 */
 
class Archiver_Command_Install extends Command {
  public function shortHelp()
  {
    return $this->line('Adds required field for the archiver plugin');
  }
  public function longHelp($params)
  {
    $this->shortHelp();
    $this->line('Usage : ');
    $this->line('plugin archiver install table1 [table2 .... tablen]');
  }
  public function execute($params,$options = array())
  {
    if(count($params)>0) {
      foreach($params as $table) {
        if($this->_install($table)) {
          $toRegen = true;
        }
      }
      if($toRegen) {
        self::launch('db regen');
      }
    } else {
      return $this->error('you must provide tables to install the plugin to');
    }
  }

  protected function _install($table) {
    $db = MDB2::singleton(DB_URI);    
    $ret = $db->exec('ALTER TABLE `'.$table.'` ADD `archiver_archived` BOOL NOT NULL');
    if(!PEAR::isError($ret)) {
      $this->line('added field to '.$table);
      return true;
    } else {
      $this->line('Field already added to '.$table);
    }
    
  }
}    