<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   db.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Various utilities related to DB_DataObject
 */

class Command_Db extends Command {
  public function shortHelp()
  {
    $this->line('utilities related to Data Access Objects and database');
  }
  public function longHelp()
  {
    $this->line('Various utilities related to Data Access Objects and database');
    $this->line('Usage:');
    $this->line('db regen');
    $this->line("\t".'Regenerates DOclasses');
    $this->line('db backup [filename]');
    $this->line("\t".'Creates a .sql.gz backup file for the current database. If filename is not provided, the resulting file will be named database_name_YYYY-mm-dd. file extension is automatically appendend and therefore not needed.');
    $this->line("\t".'Usage: db backup my_backup');
    $this->line('db restore filename');
    $this->line("\t".'Restores the database from either a .sql.gz or a .sql dump file. File extension is automatically appended if not provided and therefore not needed');
    $this->line("\t".'Usage: db restore my_backup');
    $this->line("\t\t".'db restore my_backup.sql.gz');    
    $this->line('Both examples above will have the same result');
  }
  public function execute($params)
  {
    $meth = array_shift($params);

    $method = 'execute'.$meth;
    if(method_exists($this,$method)) {
      call_user_func_array(array($this,$method),array($params));
    }
  }
  public function executeRegen($params)
  {
    $this->line('Regenerating DOclasses');
    require_once('M/DB/DataObject/Advgenerator.php');
	$generator = new DB_DataObject_Advgenerator();
	$generator->start();
	$this->line('DOclasses need to be reloaded so ...');
	$this->launch('reboot');
  }
  public function executeBackup($params = array())
  {
    $filename = $params[0];
    $this->_dobackup($filename);
  }

  public function executeBackupnrotate($params = array())
  {
    for($i=7;$i>0;$i--) {
      $origfile = APP_ROOT."backups/".'j-'.$i.'.sql.gz';
      $destfile = APP_ROOT."backups/".'j-'.($i+1).'.sql.gz';      
      @rename($origfile,$destfile);
    }

    rename(APP_ROOT.'backups/latest.sql.gz',APP_ROOT.'backups/j-1.sql.gz');
    if(date('w')==1 || in_array('week',$params)) {
      for($i=4;$i>0;$i--) {
        $origfile = APP_ROOT."backups/".'s-'.$i.'.sql.gz';
        $destfile = APP_ROOT."backups/".'s-'.($i+1).'.sql.gz';      
        @rename($origfile,$destfile);
      }
      @rename(APP_ROOT.'backups/j-8.sql.gz',APP_ROOT.'backups/s-1.sql.gz');      
    }

    if(date('d')=='01' || in_array('month',$params)) {
      for($i=12;$i>0;$i--) {
        $origfile = APP_ROOT."backups/".'m-'.$i.'.sql.gz';
        $destfile = APP_ROOT."backups/".'m-'.($i+1).'.sql.gz';      
        @rename($origfile,$destfile);
      }
      @rename(APP_ROOT.'backups/s-5.sql.gz',APP_ROOT.'backups/m-1.sql.gz');
    }
    $this->_dobackup('latest');
  }
  public function _dobackup($filename)
  {
    $opt = PEAR::getStaticProperty('DB_DataObject', 'options');
    $db = MDB2::singleton($opt['database']);
    $h = $db->dsn['hostspec'];
    $u = $db->dsn['username'];
    $p = $db->dsn['password'];
    $dbn = $db->database_name;
    if(empty($filename)) {
      $filename = $dbn."_".date('Y-m-d');
    }
    $com = "/usr/bin/env mysqldump --host=$h --user=$u --password=$p $dbn > ".APP_ROOT."backups/".$filename.".sql;gzip ".APP_ROOT."backups/".$filename.".sql";
    $this->line('executing SH:');
    $this->line($com);
    passthru($com);
    $this->line('Backup done : '.$filename.'.sql.gz');
    
  }
  public function executeRestore($params = array()) {
    $filename = $params[0];
    if(empty($filename)) {
      $this->error('Backup filename not provided');
      return;
    }
    if(!file_exists(APP_ROOT.'backups/'.$filename)) {
      switch(true) {
        case file_exists(APP_ROOT.'backups/'.$filename.'.sql'):
          $filename.='.sql';
        break;
        case file_exists(APP_ROOT.'backups/'.$filename.'.sql.gz'):
          $filename.='.sql.gz';
        break;          
        default:
        $this->error('File does not exist');
        return;        
      }
    }
    if(eregi('\.gz$',$filename)) {
      system('gunzip '.APP_ROOT."backups/".$filename);
      $file = eregi_replace('\.gz$','',$filename);
      $toremove=true;
    } else {
      $file = $filename;
      $toremove=false;
    }
    $opt = PEAR::getStaticProperty('DB_DataObject', 'options');
    $db = MDB2::singleton($opt['database']);
    $h = $db->dsn['hostspec'];
    $u = $db->dsn['username'];
    $p = $db->dsn['password'];
    $dbn = $db->database_name;
    $com = "cd ".APP_ROOT."backups;cat $file | /usr/bin/env mysql --host=$h --user=$u --password=$p $dbn";
    system($com);
    if($toremove) {
      passthru('gzip '.APP_ROOT.'backups/'.$file);
    }
  }
}
