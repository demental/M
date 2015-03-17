<?php

function can($action, $module, $record = null) {
  return User::getInstance('office')->getDBDO()->can($action, $module, $record);
}

function deny_unless_can($action, $module, $record = null)
{
  if(can($action, $module, $record)) return;

  throw new SecurityException(__('error.cannot_do_action_on_that', array($action, ($record ? $record : __("modules.$module.frontname")))));

}
