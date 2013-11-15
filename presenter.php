<?php
class Presenter {
  public static function create(DB_DataObject $do) {

    $class = $do->tableName().'_Presenter';

    if(!class_exists($class)) {
      $reflector = new ReflectionClass($do);
      $dofile = $reflector->getFileName();

      $file = dirname($dofile).'/../Presenters/'.ucfirst($do->tableName()).'.php';
      require_once $file;
    }

    return new $class($do);
  }

  public function __construct($do)
  {
    $this->_do = $do;
  }
  public function getDO()
  {
    return $this->_do;
  }
}