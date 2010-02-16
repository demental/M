<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Plugin_I18n
*/
/**
* M PHP Framework
*
* i18n plugin for DB_DataObject
*
* @package      M
* @subpackage   DB_DataObject_Plugin_I18n
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
 * The difference between this plugin and i18n is this one is more basic :
 * - language-based (i18n is culture-based)
 * - no behaviour fields
 */

class DB_DataObject_Plugin_I18n extends M_Plugin {
  public $plugin_name='international';
  public $_autoActions = true;
  protected $_grouped;


  public function getEvents()
  {
    return array('pregenerateform','postgenerateform','preprocessform','postprocessform','find','update','postupdate','delete','postinsert');
  }
  public function setGrouped($bool,$obj)
  {
    if($bool) {
      $this->_grouped = true;
    } else {
      $this->_grouped = false;
    }
  }
  public function preGenerateForm($fb,$obj)
  {

    if(!$this->_autoActions) {
      $langs = array(T::getLocale());
    } else {
      $langs = $this->getLocales($obj);
    }
    
    $obj->_i18ndos = $this->prepareTranslationRecords($obj,$langs);
  }
  public function getDefaultLang($obj)
  {
    return T::getLocale();
  }
  public function postGenerateForm($form,$fb,$obj){
    
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $langs = $this->getLocales($obj);
    foreach($obj->_i18ndos as $lang=>$arec) {
      if($this->_grouped) {
        $obj->_i18ndos[$lang]->fb_addFormHeader = false;
      }
      $obj->_i18nfbs[$lang] = MyFB::create($obj->_i18ndos[$lang]);

      $obj->_i18nfbs[$lang]->useForm($form);
      $obj->_i18nfbs[$lang]->getForm();


    }

    if($this->_grouped) {
      $this->postGenerateFormGrouped($form,$fb,$info,$langs,$obj);
      return;
    }
    $elements = $obj->_i18nfbs[$lang]->_reorderElements();

    if(is_array($obj->fb_fieldsToRender)) {
      $iFields = array_intersect($info,$obj->fb_fieldsToRender);
    } else {
      $iFields = $info;
    }
    $allLangs = array_diff($langs,array($this->getDefaultLang($obj)));
    array_unshift($allLangs,$this->getDefaultLang($obj));
    $langs = $allLangs;
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
  public function postGenerateFormGrouped($form,$fb,$info,$langs,$obj)
  {
    $lang = $langs[0];
      $elements = $obj->_i18nfbs[$lang]->_reorderElements();
      if(is_array($obj->fb_fieldsToRender)) {
        $iFields = array_intersect($info,$obj->fb_fieldsToRender);
      } else {
        $iFields = $info;
      }
      $allLangs = array_diff($langs,array($this->getDefaultLang($obj)));
      array_unshift($allLangs,$this->getDefaultLang($obj));
      $langs = $allLangs;

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
          $elem->setAttribute('id',$id);
          $label = $elem->getLabel();
          if(is_array($label)) {
            $sublabel = $label[0];
          } else {
            $sublabel = $label;
          }
          $elem->setLabel($sublabel.'('.$lang.')');
          $fields[] =$elem;
          $form->removeElement($completename.'_'.$lang);
          
        }
        if(!$form->elementExists('__submit__')) {
          $form->addElement('group', $completename.'_group',$label,$fields,'');
        } else {
          $form->insertElementBefore(HTML_QuickForm::createElement('group', $completename.'_group',$label,$fields),'__submit__');
        }
        if(in_array($field,$fb->fieldsRequired) || ($elements[$field] & DB_DATAOBJECT_NOTNULL)) {
            $form->addGroupRule($completename.'_group',$fb->requiredRuleMessage,'required',null,1);
        }        
      }
    }

  public function preProcessForm(&$values,$fb,$obj)
  {
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $elements = $obj->_i18nfbs[T::getLocale()]->_reorderElements();      
    // To avoid duplicate saving of current lang record
    $this->_dontSavei18n = true;
    unset($obj->i18n_lang);
    unset($obj->i18n_record_id);
    unset($obj->i18n_id);
    $obj->whereAdd();
    if($obj->pk()) {
      $db = $obj->getDatabaseConnection();
      $obj->whereAdd($db->quoteIdentifier($obj->pkName()).' = '.$db->quote($obj->pk()));
    }
  }
  
  public function postProcessForm(&$values,$fb,$obj)
  {
    foreach($this->getLocales($obj) as $lang) {      
      $obj->_i18ndos[$lang]->i18n_record_id = $obj->pk();
      $obj->_i18nfbs[$lang]->processForm($values);
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
    $info = $info['i18n'];	  
	  $out = array();
    $tablename = $obj->tableName().'_i18n';
    if(is_array($obj->fb_fieldsToRender)) {
      $iFields = array_intersect($info,$obj->fb_fieldsToRender);
    } else {
      $iFields = $info;
    }
    foreach($langs as $lang) {
      $t = DB_DataObject::factory($tablename);
      $t->i18n_lang=$lang;
      $t->i18n_record_id = $obj->pk();
      if($obj->pk()) {
        $t->find(true);
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
      $t->fb_createSubmit = false;
      $t->fb_addFormHeader = true;
      $t->fb_formHeaderText = $lang;
      $out[$lang] = $t;
    }
    return $out;
	}
  public function getLocales($obj)
  {
    if(!$this->_autoActions) {
      $langs = array(T::getLocale());
    } else {
      if(is_array($obj->_i18nlangs)) {
        $langs = $obj->_i18nlangs;
      } else {
        $langs = Config::getAllLocales();
      }
    }    
    return $langs;
  }
  public function setLangs($obj,$langs)
  {
    if(!is_array($langs)) return false;
    $obj->_i18nlangs = $langs;

    return true;
  }
	public function find($autoFetch,$obj)
	{
    $do = DB_DataObject::factory($obj->tableName().'_i18n');

    foreach($do->table() as $field=>$type) {
      $do->$field = $obj->$field;
    }
    $do->i18n_lang = T::getLocale();
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
	  
    if(!$this->_dontSavei18n) {
      $this->saveTranslation($obj,T::getLocale());
    }
	}
  public function update($originalDo=false,$obj)
  {
        $obj->whereAdd();
        $obj->_query['condition'] = '';
        
  }
	public function postUpdate($obj)
	{

    if(!$this->_dontSavei18n) {
      $this->saveTranslation($obj,T::getLocale());
    }
	}
	public function saveTranslation($obj,$lang)
	{
	  $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $do = DB_DataObject::factory($obj->tableName().'_i18n');
    $do->i18n_lang = $lang;
    $do->i18n_record_id = $obj->pk();
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
	// = i18n table Generation (not migration) =
	// =========================================
	public function generateTable($obj)
  {
    require_once 'M/DB/DataObject/Plugin/International.php';
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
    $info = $obj->_getPluginsDef();
    $info = $info['i18n'];
    $db = $obj->getDatabaseConnection();
    foreach(Config::getAllLangs() as $lang) {
      T::setLang($lang);
      
      $original = DB_DataObject::factory($obj->tableName());
      $original->unloadPlugin('international');
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
      die($res->getMessage());
    }
    $toremove = array_flip($obj->i18nFields);
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
    $info = $info['i18n'];
    if(!$obj->pk()) {
      return false;
    }
    $iname = $obj->tableName().'_i18n';
    $t = DB_DataObject::factory($iname);
    $t->i18n_record_id = $obj->pk();
    $t->i18n_lang = $lang;
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
  		$translateDO=& DB_DataObject::factory($obj->tableName().'_i18n');
  		if(PEAR::isError($translateDO)){
  			echo "Error while translating : ".$translateDO->getMessage();exit;
  		}
  		$translateDO->i18n_record_id=$obj->pk();
      $translateDO->i18n_lang = $lang;
      $translateDO->find();
  	  return $translateDO;
  }
	function &getTranslationRecords(&$obj, $lang = null) {
    if(!$obj->pk()) {return false;}

		if(empty($lang)){
			$lang=T::getLang();
		}
		$translateDO=& DB_DataObject::factory($obj->tableName().'_i18n');
		if(PEAR::isError($translateDO)){
			echo "Error while translating : ".$translateDO->getMessage();exit;
		}

		$translateDO->i18n_lang=$lang;
		$translateDO->i18n_record_id=$obj->pk();
    return $translateDO;
	}

	function getTranslations(&$obj, $lang=null) {
    $translateDO = self::getTranslationRecords($obj,$lang);
		if($translateDO->find(true)){
      $fields = $translateDO->toArray();
      unset($fields['i18n_id']);
      unset($fields['i18n_record_id']);
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
		$translateDO=& DB_DataObject::factory($obj->tableName().'_i18n');
    foreach(Config::getAllLangs() as $lang) {
		    $out[$lang] = '';
		} 
		$translateDO->i18n_record_id=$obj->pk();
		if($translateDO->find()){
			while($translateDO->fetch()) {
                $out[$translateDO->i18n_lang] = $translateDO->{$field};
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
  		$translateDO=& DB_DataObject::factory($obj->tableName().'_i18n');
  		$translateDO->i18n_record_id=$obj->pk();
      $translateDO->i18n_lang=$lang;
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
		$translateDO=& DB_DataObject::factory($obj->tableName().'_i18n');
		if(PEAR::isError($translateDO)){
			echo "Erreur lors de la traduction : ".$translateDO->getMessage();exit;
		}
		$translateDO->i18n_lang=$lang;
		$translateDO->i18n_record_id=$obj->pk();
		if($translateDO->find(TRUE)){
			return $translateDO->{$field};
		} else {
			$translateDO->{$field}=$obj->$field;
			$translateDO->insert();
			return $obj->$field;
		}
	}
}