<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Error404Exception
*/
/**
* M PHP Framework
*
* Exception thrown by Module if action not found
*
* @package      M
* @subpackage   Error404Exception
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Error404Exception extends Exception {
    function getError() {
        return print_r(ini_get('include_path'),true);
    }
}