<?php
/**
* M PHP Framework
* @package      M
* @subpackage   iOrder
*/
/**
* M PHP Framework
*
* Order interface to be implemented in whatever order has to be handled by a Payment object
*
* @package      M
* @subpackage   iOrder
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

interface iOrder {
  public function getId();
  public function retrieveById($value);
  public function success($transcript);
  public function error($err_code);
  public function getAmount();
}