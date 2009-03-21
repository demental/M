<?php
/**
* M PHP Framework
* @package      M
* @subpackage   iListener
*/
/**
* M PHP Framework
*
* Simple listener interface
*
* @package      M
* @subpackage   iListener
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

interface iListener {
    public function receiveMessage($message,$type);
}
?>