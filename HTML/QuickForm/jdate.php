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
* @subpackage   HTML_QuickForm_jdate
*/
/**
* M PHP Framework
*
* Date extension to use with jQuery jcalendar. Just adds classes that jcalendar needs
*
* @package      M
* @subpackage   HTML_QuickForm_jdate
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

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