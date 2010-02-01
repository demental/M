<?php
class PluginModule_Taghelper extends Module {
  public function getCacheId()
  {
    return false;
  }
  public function doExecIndex()
  {
    # code...
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
  public function doExecAddByStrip()
  {
    $focus = $this->getFocus();
    $focus->addTag($_REQUEST['strip']);
    if(!$this->isAjaxRequest()) {
      $this->redirect($_REQUEST['target']);
    } else {
      die('ok');
    }    
  }
  public function doExecAutocomplete()
  {
    $t = DB_DataObject::factory('tag');
    $t->find();
    $t2 = DB_DataObject::factory('tag');
    $num = $t2->count();
    if($num<100) {
      $local = true;
    }
    $this->assign('local',$local);
    $this->assign('tags',$t);
    $this->assign('field',$this->getParam('field'));
  }
}