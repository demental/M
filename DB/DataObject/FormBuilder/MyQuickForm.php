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

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class DB_DataObject_FormBuilder_MyQuickForm extends DB_DataObject_FormBuilder_QuickForm
{
  function _createFormObject($formName, $method, $action, $target)
  {
    if (!$this->_form instanceOf MyQuickForm) {
      $this->_form = new MyQuickForm($formName, $method, $action, $target, null, true);
    }
  }
  function &_createSelectBox($fieldName, $options, $multiple = false)
  {
    if ($multiple) {
      $element = MyQuickForm::createElement($this->_getQFType('multiselect'),
        $this->_fb->getFieldName($fieldName),
        $this->_fb->getFieldLabel($fieldName),
        $options,
        array('multiple' => 'multiple'));
    } else {
      $element = MyQuickForm::createElement($this->_getQFType('select'),
        $this->_fb->getFieldName($fieldName),
        $this->_fb->getFieldLabel($fieldName),
        $options);
      $attr = $this->_getAttributes('select', $fieldName);
      $element->updateAttributes($attr);
      if (isset($this->linkNewValue[$fieldName])) {
        $links = $this->_fb->_do->links();
        if (isset($links[$fieldName])) {
          list($table,) = explode(':', $links[$fieldName]);
          $element->addOption($this->linkNewValueText, $this->linkNewValueText);
          $element->updateAttributes(array('onchange' => 'db_do_fb_'.$this->_fb->getFieldName($fieldName).'__subForm_display(this)'));
          $element->updateAttributes(array('id' => $element->getName()));
          $this->_prepareForLinkNewValue($fieldName, $table);
          $subFormElement = self::createElement($this->_getQFType('subForm'),
           $this->_fb->getFieldName($fieldName).'__subForm',
           '',
           $this->_linkNewValueForms[$fieldName]);
          $subFormElement->setPreValidationCallback(array(&$subFormElement, 'preValidationCallback'));
          $subFormElement->linkNewValueText = $this->linkNewValueText;
          $subFormElement->selectName = $this->_fb->getFieldName($fieldName);
          $el =& $this->_form->addElement('hidden', $this->_fb->getFieldName($fieldName).'__subForm__displayed');
          $el->updateAttributes(array('id' => $el->getName()));
          $element = MyQuickForm::createElement('group',
            $this->_fb->getFieldName($fieldName),
            $this->_fb->getFieldLabel($fieldName),
            array($element, $subFormElement),
            '',
            false);
        }
      }
    }
    return $element;
  }
}
