<?php
class Tag_Module_Admin extends Module {
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
  public function doExecApplier()
  {
    $form = new HTML_QuickForm('applyform','POST',M_Office::URL());
    foreach(FileUtils::getAllFiles(APP_ROOT.PROJECT_NAME.'/DOclasses/','php') as $file) {
      $t = DB_DataObject::factory(strtolower(basename($file,'.php')));

      if(PEAR::isError($t)) continue;
      $plugs = $t->_getPluginsDef();
      if($plugs['tag']) {
        $opts[$t->tableName()] = $t->tableName();
      }
    }
    $form->addElement('select','table','Table',$opts);
    $form->addElement('textarea','clause','clause','rows="4" cols="60"');
    $form->addElement('text','tagname','tagname');
    $form->addElement('checkbox','distinct','distinct');    
    $form->addElement('submit','__submit__','Apply');
    $form->addRule('tagname','Please enter a tag','required');
    $form->addFormRule(array($this,'checkApplier'));
    if($form->validate()) {
      @set_time_limit(0);
      ini_set('memory_limit','1024M');
      $values = $form->exportValues();
      $t = DB_DataObject::factory($values['table']);
      $query ='SELECT '.($values['distinct']?'DISTINCT ':' ').$values['table'].'.* FROM '.$values['table'].' '.$values['clause'];
      $t->query($query);
      while($t->fetch()) {
        $t->addTag($values['tagname']);
        $applied++;
      }
      $this->assign('success',1);
      $this->assign('applied',$applied);
    }
    $this->assign('form',$form);
  }
  public function checkApplier($values)
  {

    $q = 'SELECT '.($values['distinct']?'DISTINCT ':' ').$values['table'].'.* FROM '.$values['table'].' '.$values['clause'].' LIMIT 0,1';
    $c = DB_DataObject::factory($values['table']);
    $db = $c->getDatabaseConnection();
    if(PEAR::isError($db->query($q))){
      return array('clause'=>'Erreur de requête');
    }
    return true;
  }
  
}