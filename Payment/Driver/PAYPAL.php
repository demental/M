<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_PAYPAL
*/
/**
* M PHP Framework
*
* Payment driver for PAYPAL
*
* @package      M
* @subpackage   Payment_Driver_PAYPAL
*
*/

class Payment_Driver_PAYPAL extends Payment
{
  protected $_mode = 'paypal';
  
  function __construct($options)
  {
    $this->options = $options;
  }
  function fetch() {      
      return null;
  }
  function setResponse($response) {
    $this->transcript = $response;
    $this->fillFromTranscript();
  }
  function getResponse() {
    # ...
  }
  function fillFromTranscript() {
    $this->transcript['response_code'] = $this->transcript['ACK'];
  }
  public function isSuccess()
  {
    return ($this->transcript['ACK']=='Success');
  }
  function getOrderId() {
      return $this->orderId;
  }
  public function setOrder(iOrder $order)
  {
    $this->order = $order;
    $this->orderId = $order->getId();
  }
  public function setAdditionalInfo($info)
  {
    $this->_additionalInfo = base64_encode(serialize($info));
  }
  public function getAdditionalInfo()
  {
    return unserialize(base64_decode($this->_additionalInfo));
  }
  function getTransactionId() {
      return $this->transaction_id;
  }
  public function setTransactionId($id)
  {
    $this->transaction_id=$id;
  }
  public function getResponseCode() {
    return ($this->transcript['PAYMENTSTATUS'] == 'Completed') ? '00' : $this->transcript['PAYMENTSTATUS'];
  }
}