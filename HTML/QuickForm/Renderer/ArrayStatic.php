<?php
/**
* M PHP Framework
* @package      M
* @subpackage   HTML_QuickForm_Renderer_ArrayStatic
*/
/**
* M PHP Framework
*
* QuickForm renderer for static use in template engine
*
* @package      M
* @subpackage
* @author       Arnaud Sellenet <demental@sat2way.com>
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'M/HTML/QuickForm/Renderer/Array.php';

class HTML_QuickForm_Renderer_ArrayStatic extends M_HTML_QuickForm_Renderer_Array
{

   /**
    * Current element index
    * @var integer
    */
    var $_elementIdx = 0;

    /**
    * The current element index inside a group
    * @var integer
    */
    var $_groupElementIdx = 0;

   /**
    * How to handle the required tag for required fields
    * @var string
    * @see      setRequiredTemplate()
    */
    var $_required = '';

   /**
    * How to handle error messages in form validation
    * @var string
    * @see      setErrorTemplate()
    */
    var $_error = '';


    function HTML_QuickForm_Renderer_ArraySmarty($staticLabels = false)
    {
        $this->HTML_QuickForm_Renderer_Array(true, $staticLabels);
    } // end constructor

   /**
    * Called when visiting a header element
    *
    * @param    object     An HTML_QuickForm_header element being visited
    * @access   public
    * @return   void
    */
    function renderHeader(&$header)
    {
        if ($name = $header->getName()) {
            $this->_ary['header'][$name] = $header->toHtml();
        } else {
            $this->_ary['header'][$this->_sectionCount] = $header->toHtml();
        }
        $this->_currentSection = $this->_sectionCount++;
    } // end func renderHeader

   /**
    * Called when visiting a group, before processing any group elements
    *
    * @param    object     An HTML_QuickForm_group object being visited
    * @param    bool       Whether a group is required
    * @param    string     An error message associated with a group
    * @access   public
    * @return   void
    */
    function startGroup(&$group, $required, $error)
    {
        parent::startGroup($group, $required, $error);
        $this->_groupElementIdx = 1;
    } // end func startGroup

   /**
    * Creates an array representing an element containing
    * the key for storing this
    *
    * @access private
    * @param  object    An HTML_QuickForm_element object
    * @param  bool      Whether an element is required
    * @param  string    Error associated with the element
    * @return array
    */
    function _elementToArray(&$element, $required, $error)
    {
        $ret = parent::_elementToArray($element, $required, $error);

        if ('group' == $ret['type']) {
            $ret['html'] = $element->toHtml();
            // we don't need the elements, see the array structure
            unset($ret['elements']);
        }
        if (($required || $error) && !empty($this->_required)){
            $this->_renderRequired($ret['label'], $ret['html'], $required, $error);
        }
        if ($error && !empty($this->_error)) {
            $this->_renderError($ret['label'], $ret['html'], $error);
            $ret['error'] = $error;
        }
        // create keys for elements grouped by native group or name
        if (strstr($ret['name'], '[') or $this->_currentGroup) {
            // Fix for bug #8123: escape backslashes and quotes to prevent errors
            // in eval(). The code below seems to handle the case where element
            // name has unbalanced square brackets. Dunno whether we really
            // need this after the fix for #8123, but I'm wary of making big
            // changes to this code.
            preg_match('/([^]]*)\\[([^]]*)\\]/', $ret['name'], $matches);
            if (isset($matches[1])) {
                $sKeysSub = substr_replace($ret['name'], '', 0, strlen($matches[1]));
                $sKeysSub = str_replace(
                    array('\\',   '\'',   '['  ,   ']', '[\'\']'),
                    array('\\\\', '\\\'', '[\'', '\']', '[]'    ),
                    $sKeysSub
                );
                $sKeys = '[\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $matches[1]) . '\']' . $sKeysSub;
            } else {
                $sKeys = '[\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $ret['name']) . '\']';
            }
            // special handling for elements in native groups
            if ($this->_currentGroup) {
                // skip unnamed group items unless radios: no name -> no static access
                // identification: have the same key string as the parent group
                if ($this->_currentGroup['keys'] == $sKeys && 'radio' != $ret['type']) {
                    return false;
                }
                // reduce string of keys by remove leading group keys
                if (0 === strpos($sKeys, $this->_currentGroup['keys'])) {
                    $sKeys = substr_replace($sKeys, '', 0, strlen($this->_currentGroup['keys']));
                }
            }
        // element without a name
        } elseif ($ret['name'] == '') {
            $sKeys = '[\'element_' . $this->_elementIdx . '\']';
        // other elements
        } else {
            $sKeys = '[\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $ret['name']) . '\']';
        }
        // for radios: add extra key from value
        if ('radio' == $ret['type'] && substr($sKeys, -2) != '[]') {
            $sKeys .= '[\'' . str_replace(array('\\', '\''), array('\\\\', '\\\''), $ret['value']) . '\']';
        }
        $this->_elementIdx++;
        $ret['keys'] = $sKeys;
        return $ret;
    } // end func _elementToArray

   /**
    * Stores an array representation of an element in the form array
    *
    * @access private
    * @param array  Array representation of an element
    * @return void
    */
    function _storeArray($elAry)
    {
        if ($elAry) {
            $sKeys = $elAry['keys'];
            unset($elAry['keys']);
            // where should we put this element...
            if (is_array($this->_currentGroup) && ('group' != $elAry['type'])) {
                $this->{'_currentGroup' . $sKeys} = $elAry;
            } else {
                $this->{'_ary' . $sKeys} = $elAry;
            }
        }
        return;
    }

   /**
    * Called when an element is required
    *
    * This method will add the required tag to the element label and/or the element html
    * such as defined with the method setRequiredTemplate.
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @param    boolean     The element required
    * @param    string      The element error
    * @see      setRequiredTemplate()
    * @access   private
    * @return   void
    */
    function _renderRequired(&$label, &$html, &$required, &$error)
    {
    } // end func _renderRequired

   /**
    * Called when an element has a validation error
    *
    * This method will add the error message to the element label or the element html
    * such as defined with the method setErrorTemplate. If the error placeholder is not found
    * in the template, the error will be displayed in the form error block.
    *
    * @param    string      The element label
    * @param    string      The element html rendering
    * @param    string      The element error
    * @see      setErrorTemplate()
    * @access   private
    * @return   void
    */
    function _renderError(&$label, &$html, &$error)
    {
    } // end func _renderError


   /**
    * Sets the way required elements are rendered
    *
    * You can use {$label} or {$html} placeholders to let the renderer know where
    * where the element label or the element html are positionned according to the
    * required tag. They will be replaced accordingly with the right value.	You
    * can use the full smarty syntax here, especially a custom modifier for I18N.
    * For example:
    * {if $required}<span style="color: red;">*</span>{/if}{$label|translate}
    * will put a red star in front of the label if the element is required and
    * translate the label.
    *
    *
    * @param    string      The required element template
    * @access   public
    * @return   void
    */
    function setRequiredTemplate($template)
    {
        $this->_required = $template;
    } // end func setRequiredTemplate

   /**
    * Sets the way elements with validation errors are rendered
    *
    * You can use {$label} or {$html} placeholders to let the renderer know where
    * where the element label or the element html are positionned according to the
    * error message. They will be replaced accordingly with the right value.
    * The error message will replace the {$error} placeholder.
    * For example:
    * {if $error}<span style="color: red;">{$error}</span>{/if}<br />{$html}
    * will put the error message in red on top of the element html.
    *
    * If you want all error messages to be output in the main error block, use
    * the {$form.errors} part of the rendered array that collects all raw error
    * messages.
    *
    * If you want to place all error messages manually, do not specify {$html}
    * nor {$label}.
    *
    * Groups can have special layouts. With this kind of groups, you have to
    * place the formated error message manually. In this case, use {$form.group.error}
    * where you want the formated error message to appear in the form.
    *
    * @param    string      The element error template
    * @access   public
    * @return   void
    */
    function setErrorTemplate($template)
    {
        $this->_error = $template;
    } // end func setErrorTemplate
}
?>
