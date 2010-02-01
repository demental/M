<?php
class PluginModule_Admin extends Module {
  public function getCacheId($action)
  {
    return false;
  }
  public function doExecIndex()
  {
    # code...
  }
  public function doExecMerger()
  {
    # code...
  }
  public function doExecArchiver()
  {
    $nonarc = DB_DataObject::factory('tag');
    $nonarc->whereAdd('archived!=1');
    $nonarc->find();
    $this->assign('nonarc',$nonarc);

    $arc = DB_DataObject::factory('tag');
    $arc->archived=1;
    $arc->find();
    $this->assign('arc',$arc);
  }
  public function doExecStats()
  {
    # code...
  }
}