<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_Cetelem
*/
/**
* M PHP Framework
*
* Payment driver for spanish payment gateway Sistema 4B
*
* @package      M
* @subpackage   Payment_Driver_Cetelem
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2010 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment_Driver_Cetelem extends Payment
{
  protected $_mode = 'cetelem';
  
  function __construct($options)
  {
    $this->options = $options;
  }
  function fetch() {      
    $res = '<form id="Cetelemform" method="POST" action="'.$this->getOption('request_url').'" />';
    $button = $this->getOption('button');
    if($button) {
      $res.=$button;
    } else {
      $res.='<input type="submit" name="__submit__" value="'.__('Valider').'" />';
    }
    $res.='</form>';
    return $res;
  }
  function setResponse($response) {
    $this->transcript = $response;
    $this->fillFromTranscript();
  }
  function getResponse() {
    $this->transcript = $_REQUEST;
    $this->transaction_id = substr($_REQUEST['idTransaccion'],-6);
    
    $this->fillFromTranscript();
  }
  function fillFromTranscript() {
    $this->orderId = $this->transcript['pszPurchorderNum'];
    $this->transcript['response_code'] = $this->transcript['codResultado'];
    $this->transaction_id = substr($_REQUEST['idTransaccion'],-6);
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