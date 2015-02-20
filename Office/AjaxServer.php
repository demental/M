<?php
// ============
// = not used =
// ============
class M_Office_AjaxServer extends HTML_AJAX_Server {
  // this flag must be set for your init methods to be used
  var $initMethods = true;

  // init method for my hello world class
  function initAjaxDBFieldUpdater() {
    $fieldUpdater = new M_Office_AjaxDBFieldUpdater();
    $this->registerClass($fieldUpdater,'fieldUpdater','updateField','invertBool');
  }
}
