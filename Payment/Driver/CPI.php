<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_CPI
*/
/**
* M PHP Framework
*
* Payment driver for HSBC CPI
*
* @package      M
* @subpackage   Payment_Driver_CPI
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment_Driver_CPI extends Payment
{
  protected $_mode = 'cb';
  
  function __construct($options)
  {
    $this->options = $options;
  }
  function fetch() {      
    // @todo
  }
  function setResponse($response) {
    $this->transcript = $response;
    $this->fillFromTranscript();
  }
  function getResponse() {
    $this->transcript = $_POST;
    $this->transaction_id = $_POST['OrderId'];
    
    $this->fillFromTranscript();
  }
  function fillFromTranscript() {
    $this->orderId = $this->transcript['OrderId'];
    $this->transcript['response_code'] = $this->transcript['CpiResultsCode']=='0'?'00':$this->transcript['CpiResultsCode'];
    $this->transaction_id=$this->transcript['OrderId'];
    $this->_additionalInfo = $this->transcript['MerchantData'];
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