<?php

class Tag_Command_Clearcache extends Command {
  public function shortHelp()
  {
    $this->line('reprocess tag cache for every records');
  }

  public function longHelp($params)
  {
    $this->shortHelp($params);
    $this->line('Usage:');
    $this->line('If no params provided cache will be cleared on any table that has tagging capacities');
    $this->line('');
    $this->line('  plugin tag clearcache [table1, table2]');
  }

  public function execute($params)
  {
    if(count($params) > 0) {
      $tables = $params;
    } else {
      $tables = M::tablesWithPlugin('tag');
    }
    foreach($tables as $table) {
      $records = DB_DataObject::factory($table);
      $records->find();
      $records->unloadPlugins();
      $this->line('');
      $this->line("clearing cache on {$table}");
      foreach($records as $record) {
        $record->getPlugin('tag')->clearTagCache($record);
        echo '.';
      }
    }
  }
}
