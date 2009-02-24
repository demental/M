<?php
class Module_Default extends Module {
  public function getCacheId($action)
  {
    return parent::getCacheId($action);
  }
  public function preExecuteAction($action)
  {
    # code...
  }
  public function doExecIndex()
  {
    # code...
  }
}