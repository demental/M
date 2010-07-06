<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_SIS4B
*/
/**
* M PHP Framework
*
* Payment driver for spanish payment gateway Sistema 4B
*
* @package      M
* @subpackage   Payment_Driver_SIS4B
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2010 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment_Driver_SIS4B extends Payment
{
  function __construct($options)
  {
    $this->options = $options;
  }
  function fetch() {      
    $res = '<form id="sis4bform" method="POST" action="'.$this->getOption('request_url').'" />';
    $res.='<input type="hidden" value="'.$this->getOrderId().'" />';
    $res.='<input type="hidden" value="'.$this->getOption('merchant_id').'" />';
    $res.='<input type="hidden" value="'.T::getLocale().'" />';
    $res.='<a href="#">'.__('Valider').'"</a>';
    $res.='</form>';
    $res.='<script type="text/javascript">$(function(){$("#sis4bform a").click(function(){$("#sis4bform").submit()});})</script>';
    return $res;
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