<?php
class Module_Taghelper extends Module {
  public function getCacheId()
  {
    return false;
  }
  public function doExecCloud()
  {
    # code...
  }
  
  protected function getFocus() {
    $focus = $this->getParam('focus');
    if(!is_a($focus,'DB_DataObject')) {
      $focus = DB_DataObject::factory($_REQUEST['focustable']);
      $focus->get($_REQUEST['focusid']);
    }
    return $focus;
  }
  public function doExecManager()
  {
    $focus = $this->getFocus();
    $this->assign('focus',$focus);
  }
  public function doExecRemove()
  {
    $focus = $this->getFocus();
    $tag = DB_DataObject::factory('tag');
    $tag->get($_REQUEST['tagid']);
    $focus->removeTag($tag);
    if(!$this->isAjaxRequest()) {
      $this->redirect($_REQUEST['target']);
    } else {
      die('ok');
    }
  }
}