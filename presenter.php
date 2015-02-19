<?php
class Presenter {
  public static function create(DB_DataObject $do) {

    $class = $do->tableName().'_Presenter';

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
