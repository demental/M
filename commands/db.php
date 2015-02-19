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
    $this->line('db migrate [reset]');
    $this->line("\t".'executes latest migrations. If reset, executes migrations even if no last migration date found(initial DB install)');
    $this->line('db backup [filename] [db uri constant]');
    $this->line("\t".'Creates a .sql.gz backup file for the current database. If filename is not provided, the resulting file will be named database_name_YYYY-mm-dd. file extension is automatically appendend and therefore not needed.');
    $this->line("\t".'Usage: db backup my_backup');
    $this->line('db restore filename');
    $this->line("\t".'Restores the database from either a .sql.gz or a .sql dump file. File extension is automatically appended if not provided and therefore not needed');
    $this->line("\t".'Usage: db restore my_backup');
    $this->line("\t\t".'db restore my_backup.sql.gz');
    $this->line('Both examples above will have the same result');
    $this->line("\t".'Usage: db backupnrotate [week|month|all] [db uri constant]');
    $this->line("\t\t".'creates a backup and rotates it');
  }
  public function execute($params)
  {
    $meth = array_shift($params);

    $method = 'execute'.$meth;
    if(method_exists($this,$method)) {
      call_user_func_array(array($this,$method),array($params));
    }
  }

  public function executeMigrate($params)
  {
    $migration_date = Config::getPref('migration_date', false);
    if(empty($migration_date) && $params[0]!= 'reset') {
      return $this->error('No migration date. If you want to reinstall all the migrations add reset to your command');
    }
    $this->line('migrating database changes since '.$migration_date);
    $new_migration_date = date('YmdHi');
    foreach(self::_migrations() as $date => $info) {
      $this->line('Check '.$date.' : '.$info['description']);
      if($date > $migration_date) {
        self::_launch_migration($info,$info['type']);
        Config::setPref('migration_date', $date);
      } else {
        $this->line('DONE');
      }
    }
  }

  public function executeReplay($params)
  {
    $migr = self::_migrations();
    if(!array_key_exists($params[0], $migr)) return $this->error('Migration not found');
    $info = $migr[$params[0]];
    self::_launch_migration($info,$info['type']);
  }
  public function _launch_migration($info, $type)
  {
    if($type == 'php') {
      self::_launch_php_migration($info);
    } else {
      self::_launch_sql_migration($info);
    }
  }

  public function _launch_php_migration($info)
  {
    require_once $info['file'];
    $migration = new $info['class'];
    $this->line('# Migration :: '.$info['description']);
    $migration->migrate('up');
    $this->line('Done :: '.$info['description']);
  }

  public function _launch_sql_migration($info)
  {
    $this->line('# Migration :: '.$info['description']);
    $this->line('Injecting SQL file : '.$info['file']);
    self::executeSQLFile($info['file']);
  }

  protected static function _migrations()
  {
    $out = array();
    $folders = array(APP_ROOT.'db/migrations/');
    foreach(PluginRegistry::registeredPlugins() as $plugin) {
      $folders[] = PluginRegistry::path($plugin).'db/migrations/';
    }
    foreach($folders as $folder) {
      self::_migrations_in_folder($folder, $out);
    }
    ksort($out);
    return $out;
  }

  protected static function _migrations_in_folder($folder, &$out) {
    foreach(FileUtils::getFiles($folder) as $file) {
      if(preg_match('`^(\d+)_(.+)\.(php|sql)$`', basename($file), $matches)) {
        $out[$matches[1]] = array(
          'file' => $folder.$file,
          'class' => 'Migration_'.Strings::camel($matches[2]),
          'description' => str_replace('_',' ',$matches[2]),
          'type' => $matches[3]
        );
      }
    }
  }

  public function executeRegen($params)
  {
    $this->line('Regenerating DOclasses');
    $generator = new DB_DataObject_Advgenerator();
    $generator->start();
    $this->line('DOclasses need to be reloaded so ...');
    $this->launch('reboot');
  }

  public function executeBackup($params = array())
  {
    $filename = $params[0];
    $this->_dobackup($filename, $params[1]);
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
    $this->_dobackup('latest', $params[1]);
  }
  public function _dobackup($filename, $db_uri_constant = null)
  {
    if(!is_null($db_uri_constant)) {
      if(!defined($db_uri_constant)) return $this->error($db_uri_constant.' not defined as a Database constant');
      $info = MDB2::parseDSN(constant($db_uri_constant));
      $h = $info['hostspec'];
      $u = $info['username'];
      $p = $info['password'];
      $dbn = $info['database'];
    } else {
      $opt = PEAR::getStaticProperty('DB_DataObject', 'options');
      $db = MDB2::singleton($opt['database']);
      $h = $db->dsn['hostspec'];
      $u = $db->dsn['username'];
      $p = $db->dsn['password'];
      $dbn = $db->database_name;
    }
    if(empty($filename)) {
      $filename = $dbn."_".date('Y-m-d');
    }
    $com = "/usr/bin/env mysqldump --host=$h --user=$u --password=$p $dbn > ".APP_ROOT."backups/".$filename.".sql;gzip ".APP_ROOT."backups/".$filename.".sql";
    $this->line('executing SH:');
    $this->line($com);
    passthru($com);
    $this->line('Backup done : '.$filename.'.sql.gz');
  }

  public static function executeSQLFile($file) {
    $db = MDB2::singleton(M::getDatabaseDSN());
    $h = $db->dsn['hostspec'];
    $u = $db->dsn['username'];
    $p = $db->dsn['password'];
    $dbn = $db->database_name;
    $com = "cat $file | /usr/bin/env mysql --host=$h --user=$u --password=$p $dbn";
    self::line($com);
    system($com);

  }
  public function executeRestore($params = array()) {
    $filename = $params[0];
    if(empty($filename)) {
      $this->error('Backup filename not provided');
      return;
    }
    $this->executeBackup(array('rollback_'.$filename, $params[1]));
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
      system('gunzip -f '.APP_ROOT."backups/".$filename);
      $file = eregi_replace('\.gz$','',$filename);
      $toremove=true;
    } else {
      $file = $filename;
      $toremove=false;
    }
    self::executeSQLFile(APP_ROOT."backups/".$file);
    if($toremove) {
      passthru('gzip '.APP_ROOT.'backups/'.$file);
    }
  }
}
