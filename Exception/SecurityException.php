<?php
/**
* M PHP Framework
* @package      M
* @subpackage   SecurityException
*/
/**
* M PHP Framework
*
* Exception thrown by Module if authenticated user does not have the correct credentials
*
* @package      M
* @subpackage   SecurityException
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class SecurityException extends Exception {
    function getError() {
        return print_r(ini_get('include_path'),true);
    }
}