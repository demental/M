<?php

function can($action, $module, $record = null) {
  if(in_array($action, array('add','edit','delete','view'))) {
    $opt = $action;
  } else {
    $opt = 'actions';
  }
  if($record instanceOf DB_DataObject) {
    $result = M_Office_Util::getGlobalOption($opt, 'showtable', $module);
  } elseif($module instanceOf DB_DataObject) {
    if(is_null($record)) {
      $domodule = M_Office_Util::get_module_for_do($module);
      $result = M_Office_Util::getGlobalOption($opt, 'showtable', $domodule);
    } else {
      // allow signature reverting ($record is $module)
      $result = M_Office_Util::getGlobalOption($opt, 'showtable', $record);
    }
  } else {
    $result = M_Office_Util::getGlobalOption($opt, 'showtable', $module);

  }
  if(is_array($result)) {
    return in_array($action, $result);
  }
  return $result ? true : false;
}

function deny_unless_can($action, $module, $record = null)
{
  if(can($action, $module, $record)) return;

  throw new SecurityException(__('error.cannot_do_action_on_that', array($action, ($record ? $record : __("modules.$module.frontname")))));

}