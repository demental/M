<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Module that handles record deletion.
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
* @todo         Might be a good idea to merge this with the actions module
*/


class M_Office_DeleteRecords extends M_Office_Controller {
  public function __construct($do, $delete) {
    parent::__construct();
    foreach ($delete as $deleteId) {
      $deldo = DB_DataObject::factory($do->tableName());
      $deldo->get($deleteId);
      if($deldo->delete()) {
				$this->say(__('Record # %s was deleted',array($deleteId)));
			}
      unset($deldo);
    }
	  $this->say(__('The selected records were deleted'));
	  M_Office_Util::refresh(M_Office::URL($this->_initRequest($_POST)));

	  return;
  }
	private function _initRequest($values) {
		unset($values['choice']);
		unset($values['doaction']);
		unset($values['multiple']);
		unset($values['selected']);
		unset($values['submit']);
		unset($values['choice']);
		unset($values['doaction']);
		unset($values['multiple']);
		unset($values['selected']);
		unset($values['__submit__']);
		unset($values['__actionscope']);
		return $values;		
	}
}
