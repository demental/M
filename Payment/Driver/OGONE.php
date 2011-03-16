<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_SIS4B
*/
/**
* M PHP Framework
*
* Payment driver for Ogone system
* (very rough)
*
* @package      M
* @subpackage   Payment_Driver_OGONE
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2010 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment_Driver_OGONE extends Payment
{
  protected $_mode = 'cb';
  
  function __construct($options)
  {
    $this->options = $options;
  }
  function fetch() {      
    // Not implemented yet
  }
  function setResponse($response) {
    $this->transcript = $response;
    $this->fillFromTranscript();
  }
  function getResponse() {
    //@todo sha1 (must be done in a controller for now)
    $this->transcript = $_REQUEST;
    
    $this->fillFromTranscript();
  }
  function fillFromTranscript() {
    $this->orderId = $this->transcript['orderID'];
    $this->transcript['response_code'] = in_array($this->transcript['STATUS'],array(5,9))?'00':'R'.$this->transcript['result'];
    $this->transaction_id = $this->transcript['orderID'];
  }
  public function isSuccess()
  {
    return ($this->transcript['response_code']=='00');
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
}