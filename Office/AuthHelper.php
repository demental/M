<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Abstract class to init user privileges
* Each project should implement its own privileges handling based on the API provided by this class
* This may change in future releases
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class AuthHelper {
    function getUserLevel() {
        return NORMALUSER;// or ADMINUSER
    }
    function initUserLevel() {
        User::getInstance('office')->setProperty('level',$this->getUserLevel());
    }
    function initUserPrivileges() {
        // This method should override staticProperties : choosetable,showtable,editrecord if necessary
        // And as well set username and groupId if necessary.
        // Example below :
        /*
        // Some code to fetch the data........
        // ................................... 
        User::getInstance()->setProperty('privileges',array(
                                    'choosetable'=>$chooseTableOptions,
                                    'showtable'=>$showTableOptions,
                                    'editrecord'=>$editRecordOptions
                                    ));
        User::getInstance()->setProperty('username',$this->user->login);
        User::getInstance()->setProperty('groupId',$this->user->admingroupe_id);                      
        */
     }
}
?>