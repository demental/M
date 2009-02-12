<?php
// ========================
// = International plugin
// = This should not be used in a production environment (big performance hit as it fetches each field in each record)
// = Use i18n instead
// = During development this plugin can be handy, as its main advantage is to be able to set international fields on-the-fly without
// = changing the database structure.
// = This plugin provides helper methods to migrate to i18n plugin (not reversible)
// ========================
require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_International extends DB_DataObject_Plugin {
    public $plugin_name='international';
  function postGenerateForm(&$form,&$fb,&$obj){
      if(!$this->_autoActions) return;
		$availangs=array();
		if($availangs=Config::getAlternateLangs()){
//      var_dump($obj->internationalFields);
			foreach($obj->internationalFields as $k){
				$field=$obj->fb_elementNamePrefix.$k.$obj->fb_elementNamePostfix;

				$grp=array();
				if($form->elementExists($field)){

					$original=$form->getElement($field);
					$originalid = 'autotransid_'.$field;
                    $original->updateAttributes(array('lang'=>'fr','id'=>$originalid));//TODO mettre langue par défaut
					if(!is_array($obj->wikiFields) || !in_array($k,$obj->wikiFields)){
						$grp[]=$original;
					}

					foreach($availangs as $l){
						$clone=clone($original);
							$clone->setName($field.'_'.$l);
							$originalLabel=$original->getLabel();
							if(is_array($originalLabel)){
								$originalLabel=$originalLabel[0];
							}
							if(!defined('DOPLUGIN_INTERNATIONAL_JAVASCRIPT_INCLUDED')) {
							    $javascript='  <script type="text/javascript" src="js/translate.js"></script>';
							    define('DOPLUGIN_INTERNATIONAL_JAVASCRIPT_INCLUDED',true);
							} else {
							    $javascript='';
							}
						$clone->setLabel($originalLabel.' version '.$l.$javascript);
						$clone->setValue($this->getTranslation($obj,$k,$l));
						$clone->updateAttributes(array('lang'=>$l,'class'=>($clone->getAttribute('class')?$clone->getAttribute('class').' ':'').'autotranslate source_'.$originalid,'id'=>$originalid.'_'.$l));
						$grp[]=$clone;
					}
					$originalClass = $original->getAttribute('class');
          $original->updateAttributes(array('class'=>($originalClass?$originalClass.' ':'').'translatesource'));
					if(!class_exists('HTML_QuickForm_Group')){
						require_once 'HTML/QuickForm/group.php';
					}
					$grpo=& new HTML_QuickForm_Group($field.'_group',$original->getLabel(),$grp,'');
					$form->insertElementBefore($grpo,$field);
					if(!is_array($obj->wikiFields) || !in_array($k,$obj->wikiFields)){
						$form->removeElement($field);
					}
				}
			}
		}
  }
// BUG marche pas avec reverselink  
  function preProcessForm(&$v,&$fb,&$obj) {
      if(!$this->_autoActions) return;
    if($availangs=Config::getAllLangs()) {
			foreach($obj->internationalFields as $k){
				$field=$fb->elementNamePrefix.$k.$fb->elementNamePostfix;
				if(!is_array($obj->my_wikiFields) || !in_array($k,$obj->my_wikiFields)){
					$v[$k]=$v[$k.'_group'][$field];
				}
			}	
		}
  }  

  function postProcessForm(&$v, &$fb, &$obj) {

    if(!$this->_autoActions) return;
    if($availangs=Config::getAlternateLangs()) {
			foreach($obj->internationalFields as $k){
				foreach($availangs as $l){
					$field=$fb->elementNamePrefix.$k.$fb->elementNamePostfix;
                    $val = $v[$k.'_group'][$field.'_'.$l];
                    if(empty($val)) {
                        $val = $v[$field];
                    }
					$this->setTranslation($obj, $k, $val, $l);
				}
			}
		}   
  }
  function setTranslation(&$obj,$field,$value,$lang){
		$id=$obj->sequenceKey();
		if(empty($obj->$id[0])){
			return '';
		}
		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}
		$translateDO->targettable=$obj->tableName();
		$translateDO->targetfield=$field;
		$translateDO->language=$lang;
		$translateDO->record_id=$obj->$id[0];
		if($translateDO->find(TRUE)){
            $original = clone($translateDO);
			$translateDO->translatedvalue=$value;
			$translateDO->update($original);
		} else {
			$translateDO->translatedvalue=$value;
			$translateDO->insert();
		}
	}
	function &getAllTranslationRecords(&$obj, $lang = null) {
		$id=$obj->sequenceKey();
		if(empty($obj->$id[0])){
			return '';
		}
		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}

		$translateDO->targettable=$obj->tableName();
		$translateDO->record_id=$obj->$id[0];
	    return $translateDO;
    }
	function &getTranslationRecords(&$obj, $lang = null) {
		$id=$obj->sequenceKey();
		if(empty($obj->{$id[0]})){
			return '';
		}
        $obj->debug('-----------Obtention traductions pour '.$obj->tableName().' '.$obj->{$id[0]}.'-----------');

		if(empty($lang)){
			$lang=T::getLang();
		}
		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}

		$translateDO->targettable=$obj->tableName();
		$translateDO->language=$lang;
		$translateDO->record_id=$obj->{$id[0]};
		$translateDO->selectAdd();
		$translateDO->selectAdd('targetfield,translatedvalue');
	    return $translateDO;
	}

	function getTranslations(&$obj, $lang=null) {
        $translateDO = self::getTranslationRecords($obj,$lang);
		if($translateDO->find()){
			while($translateDO->fetch()) {
				$field=$translateDO->targetfield;
				$obj->$field=$translateDO->translatedvalue;
			}
		}
	}
	/**
	 * fetches and returns an associative array of all translated values for $obj's $field for every installed
	 * languages.
	 * @param $obj      DB_DataObject
	 * @param $field    string          object's field name
	 * @return          array          translated values array('en'=>$string,'es'=>$string, etc...) 
	 * Only returns translated values (i.e. not translated field returns an empty array)
	 **/
	function getPoly(&$obj, $field) {
		$id=$obj->sequenceKey();
		if(empty($obj->$id[0])){
			return '';
		}
		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}
        foreach(Config::getAlternateLangs() as $lang) {
		    $out[$lang] = '';
		} 
		$translateDO->targettable=$obj->tableName();
		$translateDO->targetfield=$field;
		$translateDO->record_id=$obj->$id[0];
		if($translateDO->find()){
			while($translateDO->fetch()) {
                $out[$translateDO->language] = $translateDO->translatedvalue;
            }
        } 
        return $out;         
	}
	/**
	 * fetches and updates all translated values for $obj's $field for every installed
	 * languages using $arr.
	 * @param $obj      DB_DataObject
	 * @param $field    string          object's field name
	 * @param $arr      array          translated values array('en'=>$string,'es'=>$string, etc...) 
	 * @return          NULL
	 **/
	function setPoly(&$obj,$field,$arr) {
		$id=$obj->sequenceKey();
		if(empty($obj->$id[0])){
			return '';
		}
		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}
		$translateDO->targettable=$obj->tableName();
		$translateDO->targetfield=$field;
		$translateDO->record_id=$obj->$id[0];
		if($translateDO->find()){
	        while($translateDO->fetch()) {
                $original = clone($translateDO);
                $translateDO->translatedvalue = $arr[$translateDO->language];
                $translateDO->update($original);
                unset($arr[$translateDO->language]);
	        }
	    }
	    foreach($arr as $lang=>$value) {
    		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
    		$translateDO->targetfield=$field;
    		$translateDO->targettable=$obj->tableName();
    		$translateDO->record_id=$obj->$id[0];
            $translateDO->translatedvalue = $arr[$lang];
            $translateDO->language = $lang;
	        $translateDO->insert();
	    }
	}
	/**
	 * fetches and returns translated value for field $field of object $obj
	 * using the current language if not specified.
	 * @param $obj      DB_DataObject
	 * @param $field    string          object's field name
     * @param $lang     string  (optional)  lang to be fetched
	 * @return          string          translated value OR original value if not translated
	 **/	
	 function getTranslation($obj,$field,$lang=null) {
		$id=$obj->sequenceKey();
		if(empty($obj->$id[0])){
			return '';
		}
		if(empty($lang)){
			$lang=T::getLang();
		}
		$translateDO=& DB_DataObject::factory(Config::get('translate_db'));
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}

		$translateDO->targettable=$obj->tableName();
		$translateDO->targetfield=$field;
		$translateDO->language=$lang;
		$translateDO->record_id=$obj->$id[0];
		if($translateDO->find(TRUE)){
			return $translateDO->translatedvalue;

		} else {
			$translateDO->translatedvalue=$obj->$field;
			$translateDO->insert();
			return $obj->$field;
		}
	}
	function delete(&$obj) {
      if(!$this->_autoActions) return;
	    $translateDO = self::getAllTranslationRecords($obj);
        if($translateDO->find()) {
            while($translateDO->fetch()) {
                $translateDO->delete();
            }
        }
	}
	function postfetch(&$obj) {
      if(!$this->_autoActions) return;	    
		if(T::getLang()!=Config::get('defaultLang')){		
			$this->getTranslations($obj);
		}
	}
  public function migration_createI18nTable($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->query('create table '.$iname.' LIKE '.$obj->tableName());
    if(PEAR::isError($res)) {
      trigger_error($res->getMessage(),E_USER_WARNING);
      return $res;
    }
    return true;
  }
  public function migration_createI18nIndexes($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      var_dump($res);
    }

    $res = $db->manager->alterTable($iname,array(
      'remove'=>array('id'=>array()),'add'=>array('i18n_id'=>array('type'=>'integer','notnull'=>1,'default'=>0,'unsigned'=>1,'autoincrement'=>1,'primary'=>1))),false);
    if(PEAR::isError($res)) {
      return $res;
    }
    $res2 = $db->manager->alterTable($iname,array(    
      'add'=>array( 'i18n_lang'=>array('type'=>'text','length'=>2,'notnull'=>1,'default'=>'fr'),
                    'i18n_record_id'=>array('type'=>'integer','unsigned'=>1,'notnull'=>1,'default'=>0),
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
    return true;
  }
  public function migration_getNonI18nFields($obj,$iname) 
  {
    $t = $obj->table();
    $toremove = array();
    $i18n = $obj->internationalFields;
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
    $db = $obj->getDatabaseConnection();
    foreach(Config::getAllLangs() as $lang) {
      T::setLang($lang);
      $original = DB_DataObject::factory($obj->tableName());
      $original->find();
      $fieldsToInsert = array_merge(array('i18n_lang','i18n_record_id'),$obj->internationalFields);
      foreach($fieldsToInsert as $k=>$field) {
        $fieldsToInsert[$k] = $db->quoteIdentifier($field);
      }
      while($original->fetch()) {
        $valuesToInsert = array();
        foreach($original->internationalFields as $field) {
          if(is_numeric($original->{$field})) {
            $valuesToInsert[]=$original->{$field};// This might never happen... we never know
          } else {
            $valuesToInsert[]=$db->quote($original->{$field});
          }
        }
        $valuesToInsert = array_merge(array($db->quote($lang),$original->pk()),$valuesToInsert);
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
  public function migration_removeI18FieldsFromOriginal($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      die($res->getMessage());
    }
    $toremove = array_flip($obj->internationalFields);
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
    require_once('M/DB/DataObject/Advgenerator.php');
    $options = &PEAR::getStaticProperty('DB_DataObject', 'options');
    $options['generator_include_regex']= '`^('.$obj->tableName().'|'.$iname.')$`';
	  $generator = new DB_DataObject_Advgenerator();
	  $generator->start();


    foreach(array($options['class_location'].'/'.ucfirst($obj->tableName()).'.php',$options['class_location'].'/'.ucfirst($iname).'.php') as $file) {
      file_put_contents($file,str_replace('internationalFields','i18nFields',file_get_contents($file)));
    }
    return true;
  }
  public function migrateToI18n($obj)
  {
    $db = $obj->getDatabaseConnection();
    MDB2::setOptions($db,array('quote_identifier'=>true));
    $iname=$obj->tableName().'_i18n';
    $obj->begin();
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
    $res = $this->migration_removeNonI18nFields($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing non i18n fields for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_copyDataToI18n($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed adding data from translate table to '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_removeI18FieldsFromOriginal($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing i18n fields from '.$obj->tableName().' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    // All table manipulations were working ok, we can commit the transaction.
    $this->migration_rebuildObjects($obj,$iname);
    $obj->commit();
    
    return true;
  }
}