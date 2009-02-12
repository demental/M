<?php
// ============
// = not used =
// ============
require_once 'HTML/AJAX/Server.php';
class M_Office_AjaxServer extends HTML_AJAX_Server {
        // this flag must be set for your init methods to be used
        var $initMethods = true;

        // init method for my hello world class
        function initAjaxDBFieldUpdater() {
                require_once 'M/DB/DataObject/FormBuilder/FrontEnd/AjaxDBFieldUpdater.php';
                $fieldUpdater = new M_Office_AjaxDBFieldUpdater();
                $this->registerClass($fieldUpdater,'fieldUpdater','updateField','invertBool');
        }
}
