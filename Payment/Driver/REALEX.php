<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_REALEX
*/
/**
* M PHP Framework
*
* Payment driver for HSBC CPI
*
* @package      M
* @subpackage   Payment_Driver_CPI
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment_Driver_REALEX extends Payment
{
  protected $_mode = 'realex';
  
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
    $this->transaction_id = $_POST['ORDER_ID'];
  
    $this->fillFromTranscript();
  }
  function fillFromTranscript() {
    $this->orderId = $this->transcript['ORDER_ID'];
    $this->transcript['response_code'] = $this->transcript['RESULT'];
    $this->transaction_id=$this->transcript['ORDER_ID'];
    $this->_additionalInfo = '';
  }
  public function isSuccess()
  {
    return ($this->transcript['RESULT']=='00');
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