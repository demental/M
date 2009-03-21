<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Abstract class for listview handlers
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_View_List
{
    var $_controller;
    var $_view;
    var $_JS;
    var $_includeJS;
    #   Constructor
    function M_Office_View_List ( &$controller )
    {
        $this->_controller = $controller;
    }
    ### 
    function getControllerOption($opt,$module = null) 
    {
        return $this->_controller->getOption($opt,$module);
    }
    function getPaging() {
        return;
    }
    function toHtml() {
        return;
    }
    function setOptions($opts) {
        return;
    }
    function &prepare(&$do, $frontend = true,$pager = true) {
        return;
    }
    function getFields() {
      return $this->fields;
    }

}
?>