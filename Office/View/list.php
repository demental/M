<?php

#doc
#   classname:  M_Office_View_List
#   scope:      ABSTRACT
#
#/doc

class M_Office_View_List
{
    var $_controller;
    var $_view;
    var $_JS;
    var $_includeJS;
    #   Constructor
    function M_Office_View_List ( &$controller )
    {
        $this->_controller = $controller;
    }
    ### 
    function getControllerOption($opt,$module = null) 
    {
        return $this->_controller->getOption($opt,$module);
    }
    function getPaging() {
        return;
    }
    function toHtml() {
        return;
    }
    function setOptions($opts) {
        return;
    }
    function &prepare(&$do, $frontend = true,$pager = true) {
        return;
    }
    function getFields() {
      return $this->fields;
    }

}
?>