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
    foreach ($delete as $deleteId => $value) {
      $deldo = clone($do);
      $deldo->get($deleteId);
      if (isset($_REQUEST['choice']) && substr($_REQUEST['choice'], 0, 3) == __('Yes')) {
        if($deldo->delete()) {
					$this->say(__('Record # %s was deleted',array($deleteId)));
				}
        unset($deldo);
      } else {
        $this->showRecordToDelete($do, $this->getOption('deleteRecursive',$table));
      }
    }
	  $this->_initRequest();
	  $this->say(__('The selected records were deleted'));
	  return;
  }
	private function _initRequest() {
		unset($_POST['choice']);
		unset($_POST['doaction']);
		unset($_POST['multiple']);
		unset($_POST['selected']);
		unset($_POST['submit']);
		unset($_REQUEST['choice']);
		unset($_REQUEST['doaction']);
		unset($_REQUEST['multiple']);
		unset($_REQUEST['selected']);
		unset($_REQUEST['submit']);
	}
}
