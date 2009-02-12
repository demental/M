<?php
// ======================================================================================
// = Date extension to use with jQuery jcalendar. Just add classes that jcalendar needs =
// ======================================================================================
require_once 'HTML/QuickForm/date.php';
class HTML_QuickForm_jdate extends HTML_QuickForm_date {
  function HTML_QuickForm_jdate($elementName = null, $elementLabel = null, $options = array(), $attributes = null)
  {
    $this->HTML_QuickForm_date($elementName, $elementLabel, $options, $attributes);
    $this->_type = 'jdate';
  }  
    
  function _createElements() {
    parent::_createElements();
    foreach($this->_elements as $elem) {
      $name = $elem->getName();
      if(in_array($name,array('d','D'))) {
        $elem->setAttribute('class','jcalendar-select-day');
        continue;
      }
      if(in_array($name,array('M','m'))) {
        $elem->setAttribute('class','jcalendar-select-month');        
        continue;
      }
      if(in_array($name,array('Y','y'))) {
        $elem->setAttribute('class','jcalendar-select-year');        
        continue;
      }
    }
  }
  function toHtml(){
    return '<div class="jcalendar-selects">'.parent::toHtml().'</div>';
  }
}

?>