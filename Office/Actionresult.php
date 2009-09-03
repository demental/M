<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Actionresult.php
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * container for M_Office_Action result
 */

class M_Office_Actionresult {

  /**
   * @public string
   * stores the status of an action (error,complete,partial)
   */
  public $status;

  /**
   * @public int
   * if status is set to 'partial' containing the offset we started from (multi-page action)
   */
  public $start;

  /**
   * @public int
   * if status is set to 'partial' containing the offset we must start from in next call (multi-page action)
   */
  public $next;

  /**
   * @public int
   * if status is set to 'partial' containing the total count of elements on which we apply the action (multi-page action)
   */
  public $total;

  /**
   * @public string
   * set to 'list','record' or 'nextaction'
   */
  public $target;
  /**
   * @public string
   * name of the next action if provided (if target is set to 'nextaction')
   */
  public $nextaction;
  /**
   * @public mixed
   * parameters for the action
   */
  public $params;

  /**
   * @protected DB_DataObject
   * Containing the whole collection of elements on which to apply the action globally (multi-page)
   * the query must NOT be executed yet on this object.
   */
  protected $_selected;
   
  /**
   * @protected DB_DataObject
   * Containing the collection of elements on which to apply the action from this call only
   * the query MUST has been executed on this object
   * 
   */
  protected $_applyto;

  /**
   * @protected M_Office_Actions a reference to the caller
   */
  protected $_controller;


  public function __construct($controller)
  {
    $this->_controller = $controller;
  }
  /**
   * selected setter and validator
   * @param $selected DB_DataObject
   * @throws Exception if the query was executed already on this object
   */
  public function setSelected(DB_DataObject $selected)
  {
    if($selected->N) {
      throw new Exception('Selected elements already queried !');
    }
    $this->_selected = $selected;
  }
   
  /**
   * Applyto setter and validator
   * @param $applyto DB_DataObject
   * @throws Exception if no query was executed on this object
   */
  public function setApplyto(DB_DataObject $applyto)
  {
    if(!$applyto->N) {
        throw new Exception('Applyto elements not queried yet !');
    }
    $this->_applyto = $applyto;
  }
  
  /**
   * In case status is not 'error', this method 
   * figures out wether the action was applied to the whole resultset
   * or wether there is yet more records to apply the action to.
   * Thus sets the $status property to partial or complete.
   */
  public function processBatchStatus()
  {
    
    if(is_null($this->_controller->__step)) {
      $this->status = 'complete';
      return;
    }
    $totalcount = clone($this->_selected);
    $totalcount = $totalcount->count();
    $currentcount = $this->_controller->__start+$this->_controller->__step;
    if($currentcount<$totalcount) {
      $this->status = 'partial';
      $this->start = $this->_controller->__start;
      $this->next = $currentcount;
      $this->total = $totalcount;
    } else {
      $this->status='complete';
    }
  }
}