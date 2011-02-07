<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* This controller is called by the tree view when drag-and-drop sorting
* done by the user
* 
* 
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
* @todo         This solution is not really clean, should be refactored.
*/
*/

class M_Office_treesort extends M_Office_Controller
{    
    function M_Office_treesort() {
        M_Office_Controller::M_Office_Controller();
    }
    function processRequest() {
        $do = $this->doForTable($_REQUEST['table']);
        $do->unloadPlugin('tree');
        $arrayname=$_REQUEST['parent'];
        $cnt=0;
        foreach($_REQUEST[$arrayname] as $elt) {
          $ddo=clone($do);
          $ddo->get($elt);
          $ddo->{$ddo->treeFields['sort']}=$cnt;
          $ddo->update();
          $cnt++;
          unset($ddo);
        }
        $do = $this->doForTable($_REQUEST['table']);
        $do->getPlugin('tree')->rebuild($do);
        echo 'ok pour sort';exit;
     }
 }