<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Payment
 * @author       Arnaud Sellenet <demental at github>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Credit card payment process abstraction - draft (currently includes a driver for ATOS SIPS)
 *
 */
class Payment {
	public function &factory($driver='SIPS')
	{
	  $driver = strtoupper($driver);
		$className = 'Payment_Driver_'.$driver;
		$options = PEAR::getStaticProperty('Payment_process','options');
		return new $className($options);
	}
	public function setOptions($arr)
	{
	 $this->options = $arr;
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
		$mail->sendTo('demental at github');
		return;
	}
  public function getMode() {
    return $this->_mode;
  }
  public function setMode($mode) {
    $this->_mode = $mode;
  }
  public function getResponseCode() {
    return $this->transcript['response_code'];
  }
}
