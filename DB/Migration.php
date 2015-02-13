<?php

class DB_Migration {

  public function migrate($direction = 'up')
  {
    try {
      DB_Migration::begin();
      if($direction == 'up') {
        $this->up();
      } else {
        $this->down();
      }
      DB_Migration::commit();
    } catch(Exception $e) {
      DB_Migration::rollback();
      throw $e;
    }
  }

  public static function db()
  {
    return MDB2::singleton(M::getDatabaseDSN(), array('debug' => 1));
  }

  public static function exec($query)
  {
    $result = self::DB()->exec($query);
    if(PEAR::isError($result)) {
      throw new Exception($result->getMessage().' on query '.$query);
    }
  }

  public static function begin()
  {
    self::DB()->beginTransaction();
  }

  public static function rollback()
  {
    self::DB()->rollback();
  }

  public static function commit()
  {
    self::DB()->commit();
  }


}
