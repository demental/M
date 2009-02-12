<?php
// =======================================================
// = DB_DataObject_FormBuilder quickform driver override
// = creates a MyQuickForm object instead of HTML_QuickForm
// =======================================================
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