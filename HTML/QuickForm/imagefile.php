<?php
/**
* M PHP Framework
* @package      M
* @subpackage   HTML_QuickForm_imagefile
*/
/**
* M PHP Framework
*
* HTML class creating a file input element for uploading an image
*
* @package      M
* @subpackage   HTML_QuickForm_imagefile
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once("HTML/QuickForm/file.php");

class HTML_QuickForm_imagefile extends HTML_QuickForm_file
{
    // {{{ properties

        /**
         * Image path, if set
         **/
         var $_imagepath=null;

    // }}}
    // {{{ constructor

    /**
     * Class constructor
     * 
     * @param     string    Input field name attribute
     * @param     string    Input field label
     * @param     mixed     (optional)Either a typical HTML attribute string 
     *                      or an associative array + the path where the final image will be uploaded 
     * @since     1.0
     * @access    public
     */
    function HTML_QuickForm_imagefile($elementName=null, $elementLabel=null, $attributes=null,$path = null)
    {
        $this->_imagepath=$path;
        HTML_QuickForm_file::HTML_QuickForm_file($elementName, $elementLabel, $attributes);
            $this->setType('file');
            if(!$this->attributes['showimage']) {
              $this->updateAttributes(array('showimage'=>false));
            } else {
              $this->updateAttributes(array('showimage'=>true));          
            }
        } //end constructor

        function setValue($value)
        {
            return HTML_QuickForm_Input::setValue($value);
        }

        function toHtml() {
            $v=$this->getAttribute('value');
            if(!empty($v) && $this->getAttribute('showimage')) {
                $img='<img src="'.$this->_imagepath.$v.'" /><br />';
            } else {
                $img='';
            }
            return $img.parent::toHtml();
        }
    
    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param     string    Name of event
     * @param     mixed     event arguments
     * @param     object    calling object
     * @since     1.0
     * @access    public
     * @return    bool
     */
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'updateValue':
                if ($caller->getAttribute('method') == 'get') {
                    return PEAR::raiseError('Cannot add a file upload field to a GET method form');
                }
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    $value = $this->_findValue($caller->_submitValues);
                    if (null === $value) {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                $caller->updateAttributes(array('enctype' => 'multipart/form-data'));
                $caller->setMaxFileSize();
                break;
            case 'addElement':
                $this->onQuickFormEvent('createElement', $arg, $caller);
                return $this->onQuickFormEvent('updateValue', null, $caller);
                break;
            case 'createElement':
                $className = get_class($this);
                $this->$className($arg[0], $arg[1], $arg[2], $arg[3]);
                break;
        }
        return true;
    } // end func onQuickFormEvent    

    function _findValue(&$values)
    {
        if(is_null(parent::_findValue())){
            return HTML_QuickForm_Input::_findValue($values);
        }
    }
        
} // end class HTML_QuickForm_file
?>
