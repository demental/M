<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

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

require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_I18n extends DB_DataObject_Plugin {
  public $plugin_name='international';
  public $_autoActions = true;
  public function preGenerateForm($fb,$obj)
  {
    if(!$this->_autoActions) {
      $langs = array(T::getLang());
    } else {
      $langs = $this->getLangs();
    }
    
    $this->_dos = $this->prepareTranslationRecords($obj,$langs);
  }
  public function getDefaultLang($obj)
  {
    return T::getLang();
  }
  public function postGenerateForm($form,$fb,$obj){
    if(!$this->_autoActions) {
      $langs = array(T::getLang());
    } else {
      $langs = $this->getLangs();
    }    
    foreach($this->_dos as $lang=>$arec) {
      $this->_fbs[$lang] = MyFB::create($this->_dos[$lang]);
      $this->_fbs[$lang]->useForm($form);
      $this->_fbs[$lang]->getForm();
    }

      if(is_array($obj->fb_fieldsToRender)) {
        $iFields = array_intersect($obj->i18nFields,$obj->fb_fieldsToRender);
      } else {
        $iFields = $obj->i18nFields;
      }
      $allLangs = array_diff($langs,array($this->getDefaultLang($obj)));
      array_unshift($allLangs,$this->getDefaultLang($obj));
      $langs = $allLangs;
      foreach($iFields as $field) {
        $fields = array();
        foreach($langs as $lang) {
          $elem = $form->getElement($obj->fb_elementNamePrefix.$field.$obj->fb_elementNamePostfix.'_'.$lang);
          $elem->setAttribute('rel',$field);
          if($lang == $this->getDefaultLang($obj)) {
            $class='translatesource field_'.$lang;
            $id = 'autotransid_'.$field;
          } else {
            $class='autotranslate source_autotransid_'.$field.' field_'.$lang;
            $id = 'autotransid_'.$field.'_'.$lang;
          }
          $elem->setAttribute('class',$elem->getAttribute('class').($elem->getAttribute('class')?' ':'').$class);
          $elem->setAttribute('id',$id);
          $fields[] =$elem;
          $form->removeElement($obj->fb_elementNamePrefix.$field.$obj->fb_elementNamePostfix.'_'.$lang);
          $label = $elem->getLabel();
        }
        if(!$form->elementExists('__submit__')) {
          $form->addElement('group', $field.'_group',$label,$fields,'');
        } else {
          $form->insertElementBefore(HTML_QuickForm::createElement('group', $field.'_group',$label,$fields),'__submit__');
        }
        
      }
  }
  
  public function preProcessForm(&$values,$fb,$obj)
  {
    foreach($obj->i18nFields as $field) {
      foreach($this->getLangs() as $lang) {
        $values[$field.'_'.$lang] = $values[$field.'_group'][$field.'_'.$lang];
      }
      unset($values[$field.'_group']);
    }
    // To avoid duplicate saving of current lang record
    $this->_dontSavei18n = true;
    unset($obj->i18n_lang);
    unset($obj->i18n_record_id);
    unset($obj->i18n_id);
  }
  
  public function postProcessForm(&$values,$fb,$obj)
  {
    foreach($this->getLangs() as $lang) {      
      $this->_dos[$lang]->i18n_record_id = $obj->pk();
      $this->_fbs[$lang]->processForm($values);
    }
  }
	public function prepareTranslationRecords($obj,$langs)
	{
	  $out = array();
    $tablename = $obj->tableName().'_i18n';
    if(is_array($obj->fb_fieldsToRender)) {
      $iFields = array_intersect($obj->i18nFields,$obj->fb_fieldsToRender);
    } else {
      $iFields = $obj->i18nFields;
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
      foreach($obj->i18nFields as $field) {
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
//          unset($t->fb_preDefElements[$key]);

        }
      }
      $t->fb_fieldsToRender = $iFields;
      if($lang != $this->getDefaultLang($obj)) {
        $t->fb_excludeFromAutoRules = $iFields;
      }
      $t->fb_createSubmit = false;
      $t->fb_addFormHeader = false;

      $out[$lang] = $t;
    }
    return $out;
	}
  public function getLangs()
  {
    if(is_array($this->_langs)) return $this->_langs;
    return Config::getAllLangs();
  }
  public function setLangs($langs)
  {
    if(!is_array($langs)) return false;
    $this->_langs = $langs;
    return true;
  }
	public function find($autoFetch,$obj)
	{
    $do = DB_DataObject::factory($obj->tableName().'_i18n');
    $do->i18n_lang = T::getLang();
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
      $this->saveTranslation($obj,T::getLang());
    }
	}
  public function update($obj)
  {
        $obj->_query['condition'] = '';
  }
	public function postUpdate($obj)
	{

    if(!$this->_dontSavei18n) {
      $this->saveTranslation($obj,T::getLang());
    }
	}
	public function saveTranslation($obj,$lang)
	{
    $do = DB_DataObject::factory($obj->tableName().'_i18n');
    $do->i18n_lang = $lang;
    $do->i18n_record_id = $obj->pk();
    if($do->find(true)) {
      $action='update';
    } else {
      $action='insert';
    }
    foreach($obj->i18nFields as $field) {
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
    $int = new DB_DataObject_Plugin_Intenational(); 
    $iname = $obj->tableName().'_i18n';
    $res = $int->migration_createI18nTable($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $int->migration_createI18nIndexes($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed creating indexes for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $int->migration_removeNonI18nFields($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing non i18n fields for '.$iname.' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
    $res = $int->migration_removeI18FieldsFromOriginal($obj,$iname);
    if(PEAR::isError($res)) {
      trigger_error('failed removing i18n fields from '.$obj->tableName().' : '.$res->getMessage().' : '.$res->userinfo,E_USER_WARNING);
      $obj->rollback();
      return false;
    }
  }
  // =============================================================
  // = Cross-compatibility methods with old international plugin =
  // =============================================================
  function setTranslation(&$obj,$field,$value,$lang){
    if(!$obj->pk()) {
      return false;
    }
    $iname = $obj->tableName().'_i18n';
    $t = DB_DataObject::factory($iname);
    $t->i18n_record_id = $obj->pk();
    $t->i18n_lang = $lang;
    if(!$t->find(true)) {
      foreach($obj->i18nFields as $afield) {
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