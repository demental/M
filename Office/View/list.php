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