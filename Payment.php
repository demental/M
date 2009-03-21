<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment
*/
/**
* M PHP Framework
*
* Credit card payment process abstraction - draft (currently includes a driver for ATOS SIPS)
*
* @package      M
* @subpackage   Payment
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment {
  public function &factory($type='request',$driver='SIPS')
  {
    $className = 'Payment_Driver_'.$driver;
    require_once 'M/Payment/Driver/'.$driver.'.php';
    $options = PEAR::getStaticProperty('Payment_process','options');
    return new $className($options);
  }
  public function getOption($value)
  {
    return $this->options[$value];
  }
  public function setOption($var,$val)
  {
    $this->options[$var]=$val;
  }
  function fetch() {
      throw new Exception('Driver not implemented');
  }
  function getResponse() {
      throw new Exception('Driver not implemented');
  }

  function getTransactionId() {
    return $this->transaction_id;
  }
  public function setTransactionId($id) {
    $this->transaction_id=$id;
  }
  function &getOrder() {
      if(!is_a('iOrder',$this->order)) {
        $this->order = new Order;
        $this->order->retrieveFromId($this->getOrderId());
      }
    return $this->order;  
  }
  public function getTranscript()
  {
    return $this->transcript;
  }
  function logError() {
    $mail = Mail::factory('vide');
    $mail->setVars(array('body'=>print_r($this,true),'subject'=>'debug CB'));
    $mail->sendTo('demental@sat2way.com');
    return;
  }
}