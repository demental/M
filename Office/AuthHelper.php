<?php
// =================
// = Abstract .... Should each project implement its own privileges handling ?
// =================
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