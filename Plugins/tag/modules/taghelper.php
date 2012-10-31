<?php
// TODO:  better feedback when record is not editable (not just die('not OK')).
class Tag_Module_Taghelper extends Module {
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
    $this->assign('module',$_REQUEST['module']);
    if(!$this->_checkEditable($focus,$_REQUEST['module'])) {
      $this->setTemplate('taghelper/viewer');
    }
  }
  public function _checkEditable($focus,$module)
  {

    if(!M_Office_Util::record_belongs_to_module($focus,$module)) {
      return false;
    }
    $edit = M_Office_Util::getGlobalOption('edit','showtable',$module);

    if(!$edit) return false;
    return true;
  }
  public function doExecRemove()
  {
    $focus = $this->getFocus();
    if(!$this->_checkEditable($focus,$_REQUEST['focusmodule'])) die('not OK');
    $tag = DB_DataObject::factory('tag');
    $tag->get($_REQUEST['tagid']);
    $focus->removeTagByHuman($tag);
    if(!$this->isAjaxRequest()) {
      $this->redirect($_REQUEST['target']);
    } else {
      die('ok');
    }
  }
  public function doExecAddByStrip()
  {
    $focus = $this->getFocus();
    if(!$this->_checkEditable($focus,$_REQUEST['focusmodule'])) die('not OK');

    $focus->addTagByHuman($_REQUEST['strip']);
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