<?php
/**
* M PHP Framework
* @package      M
* @subpackage   db_dataobject_formbuilder_myquickform
*/
/**
* M PHP Framework
*
* DB_DataObject_FormBuilder quickform driver override
* creates a MyQuickForm object instead of HTML_QuickForm
*
* @package      M
* @subpackage   db_dataobject_formbuilder_myquickform
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'M/HTML/MyQuickForm.php';
require_once 'DB/DataObject/FormBuilder/QuickForm.php';
class db_dataobject_formbuilder_myquickform extends db_dataobject_formbuilder_quickform 
{
  function _createFormObject($formName, $method, $action, $target)
  {
    if (!is_a($this->_form, 'html_quickform')) {
        $this->_form =& new MyQuickForm($formName, $method, $action, $target, null, true);
    }
  }
}