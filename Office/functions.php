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