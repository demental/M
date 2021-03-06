<?php
/**
* M PHP Framework
* @package      M
* @subpackage   iTransaction
*/
/**
* M PHP Framework
*
* Transaction interface to be implemented in whatever transaction object has to be handled by a Payment process
*
* @package      M
* @subpackage   iOrder
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

interface iTransaction {
  public function getId();
  public function retrieveById($value);
  public function success(Payment $payment_driver);
  public function error(Payment $payment_driver);
  public function getAmount();
  public function getLanguage();
  public function getCurrency();    
}