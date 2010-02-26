<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_L10n
*/
/**
* M PHP Framework
*
* l10n plugin for DB_DataObject
*
* @package      M
* @subpackage   DB_DataObject_Plugin_L10n
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class DB_DataObject_Plugin_L10n extends M_Plugin {
  public $plugin_name='international';
  public $_autoActions = true;


  /**
   * @access public
   * if set to true, records will be fetched even if marked as 
   * not available (field l10n_available set in migration_addBehaviourFields)
   */
  public $_bypassAvailabilityField = false;

  public function getEvents()
  {
    return array('pregenerateform','postgenerateform','preprocessform','postprocessform','find','update','postupdate','delete','postinsert');
  }
  public function preGenerateForm($fb,$obj)
  {

    if(!$this->_autoActions) {
      $langs = array(T::getLang());
    } else {
      $langs = $this->getLangs($obj);
    }
    
    $obj->_l10ndos = $this->prepareTranslationRecords($obj,$langs);
  }
  public function getDefaultLang($obj)
  {
    return T::getLang();
  }
  public function postGenerateForm($form,$fb,$obj){
    $info = $obj->_getPluginsDef();
    $info = $info['l10n'];
    if(!$this->_autoActions) {
      $langs = array(T::getLang());
    } else {
      $langs = $this->getLangs($obj);
    }    
    foreach($obj->_l10ndos as $lang=>$arec) {
      $obj->_l10nfbs[$lang] = MyFB::create($obj->_l10ndos[$lang]);
      $obj->_l10nfbs[$lang]->useForm($form);
      $obj->_l10nfbs[$lang]->getForm();


    }
    // @ todo : the BIG todo : find a way to make a conditional form rule :
    // If a lang is marked as 'specific', check required fields
    // If a lang is marked as 'not available' or 'mirror of ...', 
    // don't check required fields for this lang.
    // for now required fields are bypassed... 
    $elements = $obj->_l10nfbs[$lang]->_reorderElements();

      if(is_array($obj->fb_fieldsToRender)) {
        $iFields = array_intersect($info,$obj->fb_fieldsToRender);
      } else {
        $iFields = $info;
      }
      $allLangs = array_diff($langs,array($this->getDefaultLang($obj)));
      array_unshift($allLangs,$this->getDefaultLang($obj));
//      $langs = $allLangs;
      foreach($iFields as $field) {
        $fields = array();
        foreach($langs as $lang) {
          $completename = $obj->fb_elementNamePrefix.$field.$obj->fb_elementNamePostfix;

          $elem = $form->getElement($completename.'_'.$lang);

          $elem->setAttribute('rel',$completename);
          if($lang == $this->getDefaultLang($obj)) {
            $class='translatesource field_'.$lang;
            $id = 'autotransid_'.$completename;
          } else {
            $class='autotranslate source_autotransid_'.$completename.' field_'.$lang;
            $id = 'autotransid_'.$completename.'_'.$lang;
          }
          $elem->setAttribute('class',$elem->getAttribute('class').($elem->getAttribute('class')?' ':'').$class);          
        }        
      }
  }
  
  public function preProcessForm(&$values,$fb,$obj)
  {
    $info = $obj->_getPluginsDef();
    $info = $info['l10n'];
    if(!$this->_autoActions) {
      $langs = array(T::getLang());
    } else {
      $langs = $this->getLangs($obj);
    }    
    
    $elements = $obj->_l10nfbs[$langs[0]]->_reorderElements();      
    // To avoid duplicate saving of current lang record
    $this->_dontSavel10n = true;
    unset($obj->l10n_lang);
    unset($obj->l10n_record_id);
    unset($obj->l10n_id);
    $obj->whereAdd();
    if($obj->pk()) {
      $db = $obj->getDatabaseConnection();
      $obj->whereAdd($db->quoteIdentifier($obj->pkName()).' = '.$db->quote($obj->pk()));
    }
  }
  
  public function postProcessForm(&$values,$fb,$obj)
  {
    $info = $obj->_getPluginsDef();
    $fields = $info['l10n'];
    foreach($this->getLangs($obj) as $lang) {
      // Alter values depending on behaviour.
      switch($values['l10n_master_culture_'.$lang]) {
        case 1://specific content.
          $obj->_l10ndos[$lang]->l10n_master_culture = null;
          $obj->_l10ndos[$lang]->l10n_available = true;
          $values['l10n_available_'.$lang]=1;
          break;
        case ''://not available
        
          $obj->_l10ndos[$lang]->l10n_master_culture = '';
          $obj->_l10ndos[$lang]->l10n_available = false;
          // we fill fields with 'n-a' to avoid not null fields to be empty
          foreach($fields as $field) {
            $slaveindex = $obj->fb_elementNamePrefix
                              .$field
                              .'_'
                              .$lang
                              .$obj->fb_elementNamePostfix;          

            $values[$slaveindex]='n-a';
          }

          break;
        default:// mirror of another language
          $obj->_l10ndos[$lang]->l10n_available = true;
          foreach($fields as $field) {
            $masterindex = $obj->fb_elementNamePrefix
                      .$field
                      .'_'
                      .$values['l10n_master_culture_'.$lang]
                      .$obj->fb_elementNamePostfix;
            $slaveindex = $obj->fb_elementNamePrefix
                              .$field
                              .'_'
                              .$lang
                              .$obj->fb_elementNamePostfix;          
            $values[$slaveindex] = $values[$masterindex];
          }
        break;
      }  
    }    
    foreach($this->getLangs($obj) as $lang) {      
      $obj->_l10ndos[$lang]->l10n_record_id = $obj->pk();
      $obj->_l10nfbs[$lang]->processForm($values);
    }

    // Patch
    foreach($this->getLangs($obj) as $lang) {      
      switch($values['l10n_master_culture_'.$lang]) {
        case 1://specific content.
          $obj->_l10ndos[$lang]->l10n_master_culture = '';
          $obj->_l10ndos[$lang]->l10n_available = true;
          $obj->_l10ndos[$lang]->update();
          break;
        case ''://not available.
        $obj->_l10ndos[$lang]->l10n_master_culture = '';
        $obj->_l10ndos[$lang]->l10n_available = false;
        $obj->_l10ndos[$lang]->update();
        break;
        default:
        $obj->_l10ndos[$lang]->l10n_available = true;
        $obj->_l10ndos[$lang]->update();
        break;
      }
    }

  }
  /**
   * Generates a FormBuilder instance for each language.
   * @params DB_DataObject main record
   * @param array languages for which to create the FormBuilders
   */
	public function prepareTranslationRecords($obj,$langs)
	{
    $info = $obj->_getPluginsDef();
    $info = $info['l10n'];	  
	  $out = array();
    $tablename = $obj->tableName().'_l10n';
    if(is_array($obj->fb_fieldsToRender)) {
      $iFields = array_intersect($info,$obj->fb_fieldsToRender);
    } else {
      $iFields = $info;
    }
    foreach($langs as $lang) {
      $t = DB_DataObject::factory($tablename);
      $t->l10n_lang=$lang;
      $t->l10n_record_id = $obj->pk();
      if($obj->pk()) {
        if(!$t->find(true)) {
          $t->l10n_available=1;
          if($lang!=T::getLang()) {
            $t->l10n_master_culture = T::getLang();
          }
        }
      }
      foreach(get_object_vars($obj) as $var=>$val) {
        if(preg_match('`^fb_`',$var)) {
          $t->$var = $val;
        }
      }

      $t->fb_elementNamePostfix.='_'.$lang;
      foreach($info as $field) {
        if(!is_array($t->fb_fieldAttributes[$field])) {
          $t->fb_fieldAttributes[$field].=($t->fb_fieldAttributes[$field]?' ':'').'lang="'.$lang.'"';        
        } else {
          $t->fb_fieldAttributes[$field]['lang'] = $lang;
        }
      }
      if(is_array($t->fb_preDefElements)) {
        foreach($t->fb_preDefElements as $key=>$elem) {
          $elem2 = clone($elem);
          if(method_exists($elem2,'updateAttributes')) {
            $elem2->updateAttributes(array('name'=>$elem2->getAttribute('name').'_'.$lang,'lang'=>$lang));
          }
          $t->fb_preDefElements[$key] = $elem2;


        }
      }
      $t->fb_fieldsToRender = $iFields;
      $t->fb_fieldsToRender[]='l10n_master_culture';
      $t->fb_userEditableFields[]='l10n_master_culture';
      $t->fb_selectAddEmpty[]='l10n_master_culture';
      $t->fb_selectAddEmptyLabel = __('Not available'); 
      $t->fb_fieldAttributes['l10n_master_culture']='class="l10n_behaviour"';
      $t->fb_enumFields[]='l10n_master_culture';
      $t->fb_enumOptions['l10n_master_culture']['1']=__('Specific content');
      $t->fb_fieldLabels['l10n_master_culture'] = __('Behaviour for this language (%s)',array($lang));
      $t->fb_excludeFromAutoRules = $iFields;
      foreach($langs as $alang) {
        if($alang==$lang) continue;
        $t->fb_enumOptions['l10n_master_culture'][$alang] = __('Mirror of %s',array($alang));
      }
      if(is_array($t->fb_preDefOrder)) {
        array_unshift($t->fb_preDefOrder,'l10n_master_culture');
      } else {
        $t->fb_preDefOrder = array('l10n_master_culture');
      }
      $t->fb_createSubmit = false;
      $t->fb_addFormHeader = true;
      $t->fb_formHeaderText = $lang;
      switch(true) {
        case !$t->l10n_available:
          $t->l10n_master_culture='';
          break;
        case empty($t->l10n_master_culture):
        $t->l10n_master_culture=1;
        break;  
      }
      $out[$lang] = $t;
    }
    return $out;
	}
  public function getLangs($obj)
  {
    if(is_array($obj->_l10nlangs)) return $obj->_l10nlangs;
    return Config::getAllLangs();
  }
  public function setLangs($obj,$langs)
  {
    if(!is_array($langs)) return false;
    $obj->_l10nlangs = $langs;
    return true;
  }
	public function find($autoFetch,$obj)
	{
    $do = DB_DataObject::factory($obj->tableName().'_l10n');

    foreach($do->table() as $field=>$type) {
      $do->$field = $obj->$field;
    }
    $do->l10n_lang = T::getLang();
    if(!$this->_bypassAvailabilityField) {
      $do->l10n_available=1;
    }
    $obj->joinAdd($do);
	}
	public function delete($obj) {
      if(!$this->_autoActions) return;
	    $translateDO = self::getAllTranslationRecords($obj);
        if($translateDO->find()) {
            while($translateDO->fetch()) {
                $translateDO->delete();
            }
        }
	}
	public function postInsert($obj)
	{
	  
    if(!$this->_dontSavel10n) {
      $this->saveTranslation($obj,T::getLang());
    }
	}
  public function update($originalDo=false,$obj)
  {
        $obj->whereAdd();
        $obj->_query['condition'] = '';
        
  }
	public function postUpdate($obj)
	{

    if(!$this->_dontSavel10n) {
      $this->saveTranslation($obj,T::getLang());
    }
	}
	public function saveTranslation($obj,$lang)
	{
	  $info = $obj->_getPluginsDef();
    $info = $info['l10n'];
    $do = DB_DataObject::factory($obj->tableName().'_l10n');
    $do->l10n_lang = $lang;
    $do->l10n_record_id = $obj->pk();
    if($do->find(true)) {
      $action='update';
    } else {
      $action='insert';
    }
    foreach($info as $field) {
      $do->$field = $obj->$field;
    }
    $do->$action();
	}
	// =========================================
	// = l10n table Generation (not migration) =
	// =========================================
	public function generateTable($obj)
  {
    require_once 'M/DB/DataObject/Plugin/International.php';
    $iname = $obj->tableName().'_l10n';
    $res = $this->migration_createL10nTable($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_createL10nIndexes($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating indexes for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $this->migration_addBehaviourFields($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating behaviourfields for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo.' continuing...',E_USER_WARNING);
    }
    $res = $this->migration_copyDataToL10n($obj,$iname);
    $res = $this->migration_removeNonL10nFields($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing non l10n fields for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }   
    $res = $this->migration_rebuildObjects($obj,$iname);
    $res = $this->migration_removeL10FieldsFromOriginal($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing l10n fields from '.$obj->tableName().' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
  }
  public function migration_createL10nTable($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->query('create table '.$iname.' LIKE '.$obj->tableName());
    if(PEAR::isError($res)) {
      trigger_error($res->getMessage(),E_USER_WARNING);
      return $res;
    }
    return true;
  }
  public function migration_addBehaviourFields($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      return $res;
    }
    $res2 = $db->manager->alterTable($iname,array(    
      'add'=>array('l10n_master_culture'=>array('type'=>'text','length'=>4),
                  'l10n_available'=>array('type'=>'integer','length'=>1,'notnull'=>1,'default'=>0)
                  )
            ),
        false    
      );            
    if(PEAR::isError($res2)) {
      return $res2;
    }
    return true;    
  }
  public function migration_createL10nIndexes($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      var_dump($res);
    }

    $res = $db->manager->alterTable($iname,array(
      'remove'=>array('id'=>array()),'add'=>array('l10n_id'=>array('type'=>'integer','notnull'=>1,'default'=>0,'unsigned'=>1,'autoincrement'=>1,'primary'=>1))),false);
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
      'add'=>array( 'l10n_lang'=>array('type'=>'text','length'=>4,'notnull'=>1,'default'=>'frfr'),
                    'l10n_record_id'=>array_merge($foreignkeyspecs,array('notnull'=>1,'default'=>0)),
                  )
                ),false
              );
    if(PEAR::isError($res2)) {
      return $res2;
    }

    $res3 = $db->createIndex($iname,'l10n',array('fields'=>array('l10n_lang'=>array(),'l10n_record_id'=>array())));
    if(PEAR::isError($res3)) {
      return $res3;
    }
    return true;
  }
  public function migration_getNonL10nFields($obj,$iname) 
  {
    $info = $obj->_getPluginsDef();
    $info = $info['l10n'];
    $t = $obj->table();
    $toremove = array();
    $l10n = $info;
    $keys = $obj->keys();
    foreach($t as $field=>$info) {
      if(!in_array($field,$l10n) && $field!=$keys[0]) {
        $toremove[$field] = array();
      }
    }
    return $toremove;
  }
  public function migration_removeNonL10nFields($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $db->loadModule('manager',null,true);
    $res = $db->manager->alterTable($iname,array('remove'=>$this->migration_getNonL10nFields($obj,$iname)),false);
    if(PEAR::isError($res)) {
      return $res;
    }
    return true;
  }
  public function migration_copyDataToL10n($obj,$iname)
  {
    $info = $obj->_getPluginsDef();
    $info = $info['l10n'];
    $db = $obj->getDatabaseConnection();
    foreach(Config::getAllLangs() as $lang) {
      T::setLang($lang);
      
      $original = DB_DataObject::factory($obj->tableName());
      $original->unloadPlugin('international');
      $ifields = $info;
      unset($original->l10nFields);

      $original->find();
      $fieldsToInsert = array_merge(array('l10n_lang','l10n_record_id'),$ifields);
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
  public function migration_removeL10nFieldsFromOriginal($obj,$iname)
  {
    $db = $obj->getDatabaseConnection();
    $res = $db->loadModule('manager',null,true);
    if(PEAR::isError($res)) {
      die($res->getMessage());
    }
    $toremove = array_flip($obj->l10nFields);
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
    return true;
  }
  
  // =============================================================
  // = Cross-compatibility methods with old international plugin =
  // =============================================================
  function setTranslation(&$obj,$field,$value,$lang){
    $info = $obj->_getPluginsDef();
    $info = $info['l10n'];
    if(!$obj->pk()) {
      return false;
    }
    $iname = $obj->tableName().'_l10n';
    $t = DB_DataObject::factory($iname);
    $t->l10n_record_id = $obj->pk();
    $t->l10n_lang = $lang;
    if(!$t->find(true)) {
      foreach($info as $afield) {
        $t->{$afield} = $obj->{$afield};
      }
      $action='insert';
    } else {
      $action='update';
    }
    $t->{$field} = $value;
    $t->$action();
	}
	function &getAllTranslationRecords(&$obj, $lang = null) {
  		$translateDO=& DB_DataObject::factory($obj->tableName().'_l10n');
  		if(PEAR::isError($translateDO)){
  			echo "Error while translating : ".$translateDO->getMessage();exit;
  		}
  		$translateDO->l10n_record_id=$obj->pk();
      $translateDO->l10n_lang = $lang;
      $translateDO->find();
  	  return $translateDO;
  }
	function &getTranslationRecords(&$obj, $lang = null) {
    if(!$obj->pk()) {return false;}

		if(empty($lang)){
			$lang=T::getLang();
		}
		$translateDO=& DB_DataObject::factory($obj->tableName().'_l10n');
		if(PEAR::isError($translateDO)){
			echo "Error while translating : ".$translateDO->getMessage();exit;
		}

		$translateDO->l10n_lang=$lang;
		$translateDO->l10n_record_id=$obj->pk();
    return $translateDO;
	}

	function getTranslations(&$obj, $lang=null) {
    $translateDO = self::getTranslationRecords($obj,$lang);
		if($translateDO->find(true)){
      $fields = $translateDO->toArray();
      unset($fields['l10n_id']);
      unset($fields['l10n_record_id']);
      foreach($fields as $field=>$val) {
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
    if(!$obj->pk()) {
      return array();
    }
		$translateDO=& DB_DataObject::factory($obj->tableName().'_l10n');
    foreach(Config::getAllLangs() as $lang) {
		    $out[$lang] = '';
		} 
		$translateDO->l10n_record_id=$obj->pk();
		if($translateDO->find()){
			while($translateDO->fetch()) {
                $out[$translateDO->l10n_lang] = $translateDO->{$field};
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
    if(!$obj->pk()) {
      return array();
    }
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}
    foreach($arr as $lang=>$val) {
  		$translateDO=& DB_DataObject::factory($obj->tableName().'_l10n');
  		$translateDO->l10n_record_id=$obj->pk();
      $translateDO->l10n_lang=$lang;
      if(!$translateDO->find(true)) {
        $action='insert';
      } else {
        $action='update';
      }
      $translateDO->{$field} = $arr[$lang];
      $translateDO->$action();
    }
	}
	/**
	 * fetches and returns translated value for field $field of object $obj
	 * using the current language if not specified.
	 * @param $obj      DB_DataObject
	 * @param $field    string          object's field name
     * @param $lang     string  (optional)  lang to be fetched
	 * @return          string          translated value
	 **/	
	 function getTranslation($obj,$field,$lang=null) {
     if(!$obj->pk()) return false;

		if(empty($lang)){
			$lang=T::getLang();
		}
		$translateDO=& DB_DataObject::factory($obj->tableName().'_l10n');
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}
		$translateDO->l10n_lang=$lang;
		$translateDO->l10n_record_id=$obj->pk();
		if($translateDO->find(TRUE)){
			return $translateDO->{$field};
		} else {
			$translateDO->{$field}=$obj->$field;
			$translateDO->insert();
			return $obj->$field;
		}
	}
}