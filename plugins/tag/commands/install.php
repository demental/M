<?php
/**
 * Installing necessary data for the tag plugin to work :
 *  - 3 tables
 */

class Tag_Command_Install extends Command {
  protected $baseFolder;
  public function __construct()
  {
    $this->baseFolder = realpath(dirname(__FILE__).'/../').'/';
  }
  public function shortHelp()
  {
    $this->line('installs necessary data to use the tag plugin');
  }
  public function longHelp($params)
  {
    $this->line('installs necessary data to use the tag plugin');
    $this->line('* adds three tables to the database : tag, tag_record, tag_history');
  }
  public function execute($params)
  {
    $db = MDB2::factory(M::getDatabaseDSN());
    $h = $db->dsn['hostspec'];
    $u = $db->dsn['username'];
    $p = $db->dsn['password'];
    $dbn = $db->database_name;
    $mysqlbin = '/usr/bin/env mysql';
    $catbin = '/usr/bin/env cat';
    $file = $this->baseFolder.'src/tag.sql';
    $sys = "$catbin $file | $mysqlbin --host=$h --user=$u --password=$p $dbn";
    system($sys,$return);
    $this->line('Creating tables :');
    $this->line('* tag');
    $this->line('* tag_record');
    $this->line('* tag_history');
  }
}
