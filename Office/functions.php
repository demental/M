<?php

function can($action, $module, $record = null) {
  $opt = M_Office_Util::getGlobalOption($action, 'showtable', $module);
  return $opt ? true : false;
}