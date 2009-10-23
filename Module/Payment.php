<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Payment.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Module to handle payment gateway response
 */


class Payment_Module extends Module {
  public function getCacheId($action)
  {
    return false;
  }
  public function executeAction($action)
  {
    $driver = ereg_replace('^(success|error|autoresponse)_','',$action);
    $action = ereg_replace($driver.'$','',$action);
    $this->setParam('driver',$driver);
    parent::executeAction($action);
  }
  
}
