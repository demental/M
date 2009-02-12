<?php
// ===============================================
// = WTF is this....
// = anyway... not in use for now...
// ===============================================

class M_Office_treesort extends M_Office_Controller
{
    // @param   string the string to search for
    // @param   string (table for which to expand view)
    // Expand 
    
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