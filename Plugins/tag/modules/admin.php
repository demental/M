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
    $tags = DB_DataObject::factory('tag');
    $tags->find();
    while($tags->fetch()) {
      $opts[$tags->id] = $tags->strip;
    }
    $form = new HTML_QuickForm('mergeform','POST',M_Office::URL(),'',null,true);
    foreach($opts as $id=>$strip) {
      $form->addElement('checkbox','source['.$id.']',$strip);
    }
    foreach($opts as $id=>$strip) {
      $form->addElement('radio','target',$strip,'',$id);
    }
    $form->addElement('submit','__submit__','Merge now !');
    if($form->validate()) {
      $values = $form->exportValues();
      $db = $tags->getDatabaseConnection();
      $dest = $values['target'];
      foreach($values['source'] as $id=>$ok) {
        $db->prepare('UPDATE tag_record SET tag_id = :tag_id WHERE tag_id = :id', array('integer','integer'), MDB2_PREPARE_MANIP)
          ->execute(array('tag_id' => $dest, 'id' => $id));
        $db->prepare('UPDATE tag_history SET tag_id = :tag_id WHERE tag_id = :id', array('integer','integer'), MDB2_PREPARE_MANIP)
          ->execute(array('tag_id' => $dest, 'id' => $id));

        $db->prepare('DELETE FROM tag WHERE id = ?', array('integer'))
          ->execute($id);
      }
      $this->redirect(M_Office::URL());
    }
    $this->assign('form',$form);
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
    $opts = M::tablesWithPlugin('tag');
    $opts = array_combine($opts, $opts);
    $form->addElement('select','table','Table',$opts);
    $form->addElement('textarea','clause','clause','rows="4" cols="60"');
    $form->addElement('text','tagname','tagname');
    $form->addElement('text','tagdel','tagdel');
    $form->addElement('checkbox','distinct','distinct');
    $form->addElement('submit','__submit__','Apply');

    $form->addFormRule(array($this,'checkApplier'));
    if($form->validate()) {
      @set_time_limit(0);
      ini_set('memory_limit','1024M');
      $values = $form->exportValues();
      $t = DB_DataObject::factory($values['table']);
      $query ='SELECT '.($values['distinct']?'DISTINCT ':' ').$values['table'].'.* FROM '.$values['table'].' '.$values['clause'];
      $t->query($query);
      while($t->fetch()) {
        if($values['tagname']) {
          $t->addTag($values['tagname']);
        }
        if($values['tagdel']) {
          $t->removeTag($values['tagdel']);
        }
        $applied++;

      }
      $this->assign('success',1);
      $this->assign('applied',$applied);
    }
    $this->assign('form',$form);
  }
  public function checkApplier($values)
  {
    if(empty($values['tagname']) && empty($values['tagdel'])) {
      return array('tagname'=>'Enter either a tag to remove or a tag to add (or both)');
    }
    $q = 'SELECT '.($values['distinct']?'DISTINCT ':' ').$values['table'].'.* FROM '.$values['table'].' '.$values['clause'];
    $c = DB_DataObject::factory($values['table']);
    $db = $c->getDatabaseConnection();
    if(PEAR::isError($db->query($q))){
      return array('clause'=>'Database Query Error');
    }
    return true;
  }
  public function doExecManager()
  {
    $t = DB_DataObject::factory('tag');
    $t->orderBy('strip ASC');
    $t->find();
    $this->assign('tags',$t);
  }
  public function doExecSwitchlock()
  {
    if(empty($_GET['id'])) $this->redirect(M_Office::URL('tag:admin/index'));
    $t = DB_DataObject::factory('tag');
    $t->id= $_GET['id'];
    if($t->find(true)) {
      $t->archived = !$t->archived;
      $t->update();
    }
    if($this->isAjaxRequest()) {
      return $this->setOuput('OK');
    } else {
      $this->redirect(M_Office::URL('tag:admin/manager',array(),array_keys($_REQUEST)));
    }
 }
 public function doExecDelete()
 {
  if(empty($_GET['id'])) $this->redirect(M_Office::URL('tag:admin/index'));
  $t = DB_DataObject::factory('tag');
  $t->id= $_GET['id'];
  if($t->find(true)) {

    $th = DB_DataObject::factory('tag_record');
    $th->tag_id = $t->id;
    $th->delete();
    $t->getDatabaseConnection()
      ->prepare('DELETE FROM tag_history WHERE tag_id = ?', 'integer', MDB2_PREPARE_MANIP)
      ->execute($t->id);

     $t->delete();
   }
   if($this->isAjaxRequest()) {
     return $this->setOuput('OK');
   } else {
     $this->redirect(M_Office::URL('tag:admin/manager',array(),array_keys($_REQUEST)));
   }
 }
 public function doExecRedirect()
 {
   $redirmodule = $_REQUEST['targetmodule'];
   $extag = explode(',',$_REQUEST['ex_tag']);
   $intag = explode(',',$_REQUEST['int_tag']);
   foreach($extag as $tagname) {
     $t = DB_DataObject::factory('tag');
     $t->strip = $tagname;
     if($t->find(true)) {
       $extagid[$t->id]=1;
     }
   }
   foreach($intag as $tagname) {
     $t = DB_DataObject::factory('tag');
     $t->strip = $tagname;
     if($t->find(true)) {
       $intagid[$t->id]=1;
     }
   }
   $this->redirect(M_Office::URL(array('module'=>$redirmodule,'exc__tags'=>$extagid,'_tags'=>$intagid),array('targetmodule','ex_tag','int_tag')));
 }
}
