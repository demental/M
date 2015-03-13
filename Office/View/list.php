<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Abstract class for listview handlers
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_View_List
{
    var $_controller;
    var $_view;
    var $_JS;
    var $_includeJS;
    #   Constructor
    public function __construct ( &$controller )
    {
        $this->_controller = $controller;
    }
    ### 
    protected function getControllerOption($opt,$module = null) 
    {
        return $this->_controller->getOption($opt,$module);
    }
    public function getPaging() {
        return;
    }
    public function toHtml() {
        return;
    }
    public function setOptions($opts) {
        return;
    }
    public function &prepare(&$do, $frontend = true,$pager = true) {
        return;
    }
    public function getFields() {
      return $this->fields;
    }

}
?>