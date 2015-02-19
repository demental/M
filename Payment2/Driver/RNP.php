<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_SIPS
*/
/**
* M PHP Framework
*
* Payment driver for FIANET Receive&Pay
*
* @package      M
* @subpackage   Payment_Driver_RNP
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment2_Driver_RNP extends Payment
{
  
  function __construct($options)
  {
    $this->options = $options;
  }
  public function fetch() {      
  }
  public function getAdditionalInfo()
  {
    return null;
  }
  public function isSuccess($success = null)
  {
    if(!is_null($success)) {
      $this->_success = $success;
    }
    return $this->_success;
  }
}
