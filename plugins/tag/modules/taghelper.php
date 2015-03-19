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
    if(!can('update', $_REQUEST['module'], $focus)) {
      $this->setTemplate('taghelper/viewer');
    }
  }
  public function doExecHistory()
  {
    $focus = $this->getFocus();
    $this->assign('focus',$focus);
    $this->assign('module',$_REQUEST['module']);
    $history = DB_DataObject::factory('tag_history');
    $history->record_id = $focus->pk();
    $history->tagged_table = $focus->tableName();
    $history->orderBy('date DESC');
    $history->find();
    $this->assign('history', $history);
  }

  public function doExecRemove()
  {
    $focus = $this->getFocus();
    deny_unless_can('update', $_REQUEST['focusmodule'], $focus);

    $tag = DB_DataObject::factory('tag');
    $tag->get($_REQUEST['tagid']);
    $focus->removeTagByHuman($tag);
    if(!$this->isAjaxRequest()) {
      $this->redirect($_REQUEST['target']);
    } else {
      return $this->setOuput('ok');
    }
  }

  public function doExecAddByStrip()
  {
    $focus = $this->getFocus();
    deny_unless_can('update', $_REQUEST['focusmodule'], $focus);

    $focus->addTagByHuman($_REQUEST['strip']);
    if(!$this->isAjaxRequest()) {
      $this->redirect($_REQUEST['target']);
    } else {
      return $this->setOuput('ok');
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
