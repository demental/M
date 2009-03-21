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
* Might be a good idea to merge this with the actions module
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class M_Office_DeleteRecords extends M_Office_Controller {
  function M_Office_DeleteRecords($do, $delete) {
    M_Office_Controller::M_Office_Controller();
// ==================================================================
// = TODO rewrite (delete confirmation including linked records) =
// ==================================================================
    /*
    if (!isset($_REQUEST['choice'])) {
        $this->assign('warning','Etes-vous sur de vouloir supprimer '.(count($delete) == 1 ? 'cet enregistrement' : 'les enregistrements suivants').'?');
        require_once('classes/MyQuickForm.php');
        $deleteForm =& new MyQuickForm('choiceForm', 'post', $this->getQueryParams(array(), array(), false), null, true);
        $this->addHiddenFields($deleteForm, array(), true);
        $buttons = array();
        if ($this->getOption('deleteRecursive', $table)) {
            $buttons[] = MyQuickForm::createElement('submit', 'choice', 'Oui (Données connexes aussi)');
        }
        $buttons[] = MyQuickForm::createElement('submit', 'choice', 'Oui');
        $buttons[] = MyQuickForm::createElement('submit', 'choice', 'Non');
        $deleteForm->addGroup($buttons);
        $this->localOutput['main'] .= $deleteForm->toHtml();
      } elseif ($_REQUEST['choice'] == 'No') {
			$this->say('Action annulée, aucun enregistrement n\'a été effacé');
			$this->initRequest();
			return;
      }

*/
       foreach ($delete as $deleteId => $value) {
          $deldo = clone($do);
          $deldo->get($deleteId);
          if (isset($_REQUEST['choice']) && substr($_REQUEST['choice'], 0, 3) == 'Oui') {
              if ($this->getOption('deleteRecursive', $table) && $_REQUEST['choice'] == 'Oui (Données connexes aussi)') {
                  $this->deleteLinkedRecords($do, $database);
              }
              if($deldo->delete()) {
							$this->say('L\'enregistrement '.$deleteId.' a été supprimé');
						}
                unset($deldo);
          } else {
              $this->showRecordToDelete($do, $this->getOption('deleteRecursive',$table));
          }
      }
			$this->initRequest();
			$this->say(__('The selected records were deleted'));
			return;
    }
		function initRequest() {
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
    function showRecordToDelete(&$do, $recurse = true) {
      // ================
      // = TODO rewrite =
      // ================
    }
    function deleteLinkedRecords(&$do) {
      // ================
      // = TODO rewrite =
      // ================
  	}
}
