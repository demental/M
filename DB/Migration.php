<?php

class DB_Migration {
  public static function db()
  {
    return MDB2::singleton(M::getDatabaseDSN(), array('debug' => 1));
  }
}