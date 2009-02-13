<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

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
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
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