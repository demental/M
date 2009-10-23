<?php
class Payment_Exception extends Exception {
  protected $thrower;
  protected $data;
  public function setData($data)
  {
    $this->data = $data;
  }
  public function getData()
  {
    return $data;
  }
  public function setThrower($thrower)
  {
    $this->thrower = $thrower;
  }
  public function getThrower()
  {
    return $this->thrower;
  }
}

class Payment_Request_Exception extends Payment_Exception {
  
}
class Payment_Response_Exception extends Payment_Exception {
  
}