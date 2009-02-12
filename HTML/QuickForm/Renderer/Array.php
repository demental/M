<?php

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