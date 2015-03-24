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
* @author       Arnaud Sellenet <demental at github>
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
 * The difference between this plugin and i18n is this one is more basic :
 * - language-based (i18n is culture-based)
 * - no behaviour fields
 */

class Plugins_I18n_db extends M_Plugin {
  public $plugin_name='i18n';
  public $_autoActions = true;
  protected $_grouped;


  public function getEvents()
  {
    return array('find','update','postupdate','delete','postinsert');
  }
  public function setGrouped($bool,$obj)
  {
    if($bool) {
      $this->_grouped = true;
    } else {
      $this->_grouped = false;
    }
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
				$obj->$field=$val;
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
		$translateDO= DB_DataObject::factory($obj->tableName().'_i18n');
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
