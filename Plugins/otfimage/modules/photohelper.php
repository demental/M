<?php

class Module_Photohelper extends Module {
  public function getCacheId($action)
  {
    return false;
  }
  public function doExecAdd()
  {
    $form = new HTML_QuickForm('addform','POST',M_Office::URL());
    $p = DB_DataObject::factory('photo');
    $p->record_table=$_GET['table'];
    $p->record_id = $_GET['record'];
    $fb = MyFB::create($p);
    $fb->useForm($form);
    $fb->getForm();
    if($form->validate()) {
//      trigger_error('form validate')
      
      $form->process(array($fb,'processForm'),false);
      $this->assign('success',1);
    }
    $this->assign('form',$form);
  }
  public function doExecList()
  {
    $p = DB_DataObject::factory('photo');
    $p->find();
    $this->assign('photos',$p);
  }
  public function doExecWidget()
  {
    $record = $this->getParam('record');
    if(!$record) {
      $record = DB_DataObject::factory($_REQUEST['table']);
      $record->get($_REQUEST['record']);
    }
    $this->assign('record',$record);
    $this->assign('photos',$record->getAllImages());
  }
  public function doExecDelete()
  {
    if(empty($_GET['record'])) die();
    $p = DB_DataObject::factory('photo');
    $p->id = $_GET['record'];
    if($p->find(true)) {
      $p->delete();
    }
    die('ok');
  }
  public function doExecSetasmain()
  {
    if(empty($_GET['record'])) die();
    $p = DB_DataObject::factory('photo');
    $p->id = $_GET['record'];
    if($p->find(true)) {
      $p->setAsMain();
    }
    die('ok');
  }
}