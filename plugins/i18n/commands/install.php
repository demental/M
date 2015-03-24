<?php
class i18n_Command_Install extends Command {
  public static function preSetup()
  {
    // Using T editor driver to avoid script termination on non-existing xml files
    // (so that whatever the order in which the extractlng and the install_i18n scripts ar run, it's ok)
    T::setConfig(array_merge(T::$config,array('driver'=>'editor')));
    return true;
  }
  public function execute($params,$options = array())
  {
    if(count($params)>0) {
      foreach($params as $table) {
        $this->_checkAndInstall($table);
      }
    } else {
      $g = new DB_DataObject_Advgenerator();
      $generators = $g->getGenerators();

      foreach($generators as $agenerator) {
        foreach($agenerator->tables as $table) {
          $this->_checkAndInstall($table);
        }
      }  
    }
  }
  
  protected function _checkAndInstall($table) {
    $t = DB_DataObject::factory($table);
    $tdef = $t->_getPluginsDef();
    if($tdef['i18n']) {
      echo $table.' has i18n fields, generating '.$table.'_i18n table'."\n";
      $this->generateTable($t);
    } else {
      $this->line($table.' : no i18n');
    }
  }

  // =========================================
  // = i18n table Generation (not migration) =
  // =========================================
  protected function generateTable($obj)
  {
    $iname = $obj->tableName().'_i18n';
    $res = $this->migration_createI18nTable($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_createI18nIndexes($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating indexes for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_copyDataToI18n($obj,$iname);
    $res = $this->migration_removeNonI18nFields($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing non i18n fields for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_rebuildObjects($obj,$iname);
    $res = $this->migration_removeI18nFieldsFromOriginal($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing i18n fields from '.$obj->tableName().' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
  }
  public function migration_createI18nTable($obj,$iname)
  {
    echo 'duplicating table';
    $db = $obj->getDatabaseConnection();
    $res = $db->query('create table '.$iname.' LIKE '.$obj->tableName());
    if(PEAR::isError($res)) {
      trigger_error($res->getMessage(),E_USER_WARNING);
      return $res;
    }
    echo 'finish duplicating table';
    return true;
  }
  public function migration_createI18nIndexes($obj,$iname)
  {
    echo 'creating indexes';
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      throw new Exception($res->getMessage());
    }

    $res = $db->manager->alterTable($iname,array(
      'remove'=>array('id'=>array()),'add'=>array('i18n_id'=>array('type'=>'integer','notnull'=>1,'default'=>0,'unsigned'=>1,'autoincrement'=>1,'primary'=>1))),false);
    if(PEAR::isError($res)) {
      return $res;
    }
    // Changing foreign key format if officepack used (CHAR(36))
    if($obj->hasplugin('officepack') || $obj->hasplugin('guid')) {
      $foreignkeyspecs = array('type'=>'text','length'=>36);
    } else {
      $foreignkeyspecs = array('type'=>'integer','unsigned'=>1);
    }

    $res2 = $db->manager->alterTable($iname,array(
      'add'=>array( 'i18n_lang'=>array('type'=>'text','length'=>2,'notnull'=>1,'default'=>'fr'),
                    'i18n_record_id'=>array_merge($foreignkeyspecs,array('notnull'=>1,'default'=>0)),
                  )
                ),false
              );
    if(PEAR::isError($res2)) {
      return $res2;
    }

    $res3 = $db->createIndex($iname,'i18n',array('fields'=>array('i18n_lang'=>array(),'i18n_record_id'=>array())));
    if(PEAR::isError($res3)) {
      return $res3;
    }
    echo 'end creating indexes';
    return true;
  }
  public function migration_getNonI18nFields($obj,$iname)
  {
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $t = $obj->table();
    $toremove = array();
    $i18n = $info;
    $keys = $obj->keys();
    foreach($t as $field=>$info) {
      if(!in_array($field,$i18n) && $field!=$keys[0]) {
        $toremove[$field] = array();
      }
    }
    return $toremove;
  }
  public function migration_removeNonI18nFields($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $db->loadModule('manager',null,true);
    $res = $db->manager->alterTable($iname,array('remove'=>$this->migration_getNonI18nFields($obj,$iname)),false);
    if(PEAR::isError($res)) {
      return $res;
    }
    return true;
  }
  public function migration_copyDataToI18n($obj,$iname)
  {
    echo 'copying data';
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $db = $obj->getDatabaseConnection();
    foreach(Config::getAllLangs() as $lang) {
      T::setLang($lang);
      $original = DB_DataObject::factory($obj->tableName());
      $original->_loadPlugins();
      echo 'unloading plugins';
      $original->unloadPlugin('i18n');
      $ifields = $info;
      unset($original->i18nFields);

      $original->find();

      $fieldsToInsert = array_merge(array('i18n_lang','i18n_record_id'),$ifields);
      foreach($fieldsToInsert as $k=>$field) {
        $fieldsToInsert[$k] = $db->quoteIdentifier($field);
      }
      while($original->fetch()) {
        echo 'fetching record num '.$original->pk()."\n";
        $valuesToInsert = array();
        foreach($ifields as $field) {
          if(is_numeric($original->{$field})) {
            $valuesToInsert[]=$original->{$field};// This might never happen... we never know
          } else {
            $valuesToInsert[]=$db->quote($original->{$field});
          }
        }
        $valuesToInsert = array_merge(array($db->quote($lang),$db->quote($original->pk())),$valuesToInsert);
        $res = $db->query('INSERT INTO '.$db->quoteIdentifier($iname).' ('.implode(',',$fieldsToInsert).') VALUES('.implode(',',$valuesToInsert).')');
        if(PEAR::isError($res)) {
          $nbfailed[$lang]++;
        }
      }
    }
    if(is_array($nbfailed)) {
      echo 'Failures while trying to insert translated data :<br />';
      foreach($nbfailed as $lang=>$nb) {
        echo $lang.' : '.$nb.'<br />';
      }
      echo '<br /><br />';
    }
    return true;
  }
  public function migration_removeI18nFieldsFromOriginal($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      throw new Exception($res->getMessage());
    }
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];

    $toremove = array_flip($info);
    foreach($toremove as $k=>$v) {
      $toremove[$k] = array();
    }
    $res = $db->manager->alterTable($obj->tableName(),array('remove'=>$toremove),false);
    if(PEAR::isError($res)) {
      return $res;
    }
    return true;
  }
  public function migration_rebuildObjects($obj,$iname)
  {
    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['generator_include_regex']= '`^('.$obj->tableName().'|'.$iname.')$`';
    $generator = new DB_DataObject_Advgenerator();
    $generator->start();
    return true;
  }

}
