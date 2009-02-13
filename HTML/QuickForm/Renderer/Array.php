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
* @subpackage   M_HTML_QuickForm_Renderer_Array
*/
/**
* M PHP Framework
*
* QuickForm renderer extension that adds a CSS class "error" to fields that are not validated
*
* @package      M
* @subpackage   M_HTML_QuickForm_Renderer_Array
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'HTML/QuickForm/Renderer/Array.php';
class M_HTML_QuickForm_Renderer_Array extends HTML_QuickForm_Renderer_Array
{
    function M_HTML_QuickForm_Renderer_Array($collectHidden = false, $staticLabels = false)
    {
        $this->HTML_QuickForm_Renderer_Array($collectHidden, $staticLabels);
    } // end constructor
    function renderElement(&$element, $required, $error)
    {
        if(!empty($error)) {
          if(is_a($element,'HTML_QuickForm_group')) {
            foreach($element->getElements() as $elem) {
              $class = $elem->getAttribute('class');
              $elem->updateAttributes(array('class'=>($class?$class.' error':'error'),'title'=>$error));              
            }
          } else {
            $class = $element->getAttribute('class');
            $element->updateAttributes(array('class'=>($class?$class.' error':'error'),'title'=>$error));
          }
        }
        $elAry = $this->_elementToArray($element, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$elAry['name']] = $error;
        }
        $this->_storeArray($elAry);
    } // end func renderElement
}
?>