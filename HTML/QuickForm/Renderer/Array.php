<?php
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
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

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
    function renderHeader(&$header)
    {
        $this->_ary['sections'][$this->_sectionCount] = array(
            'header' => $header->toHtml(),
            'name'   => $header->getName(),
            'elements' => array()
        );
        $this->_currentSection = $this->_sectionCount++;
    } // end func renderHeader

}
?>
