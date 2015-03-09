<?php


/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* listview handler using DB_DataObject_Plugin_Pager
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/



class M_Office_View_DOPaging extends M_Office_View_List
{
  #   Constructor
  public function __construct ( &$controller )
  {
      parent::__construct($controller);
  }

  ###
  public function getControllerOption($opt,$module = null)
  {
      return $this->_controller->getOption($opt,$module);
  }
  public function getPaging() {
      return $this->do->getPager()->getLinks();
  }
  public function toHtml() {
      throw new Exception('toHTML not applicable with DOPaging');
  }
  public function getTotalRecordsNum() {
    return $this->do->getPlugin('pager')->totalItems;
  }
  public function setOptions($opts) {
    $this->pagerOptions = $opts;
  }
  public function prepare($do, $module, $pager = true) {
    $do->loadPlugin('pager');

    $fb = MyFB::create($do);
    $builder = $fb->builder;
    if($builder) {
      $builder->before_form($fb);
    }
    
    $f = array_keys($do->table());
    if($do->i18nFields) {
      $f = array_merge($f,$do->i18nFields);
    }
    $this->fieldNames=array_combine($f,$f);
    if(is_array($fb->fieldLabels)){
        foreach($fb->fieldLabels as $k=>&$v){
            if(is_array($v)){
                if(key_exists('unit',$v)){
                    $unitFormatters[$k]=$v['unit'];
                }
                $v=$v[0];
            }
        }
        $this->fieldNames=array_merge($this->fieldNames,$fb->fieldLabels);
    }


    if ($pager){
      $perPage = $this->getControllerOption('recordsPerPage', $module);
      $perPage = $perPage === true ? 10 : $perPage;
    } else {
      $perPage = 100000000000;
    }
    $do->getPlugin('pager')->setOption('perPage',$perPage);

    $pk = $do->pkName();
    $AllFields=array_keys($do->table());
    $links = $do->links();
    $fields = $this->getControllerOption('fields', $module);
    $columns = $this->getControllerOption('columns', $module);
    if(!is_array($columns)) {
      $columns = $fields;
    }

    if (!is_array($fields)) {
      $fields = array_diff($AllFields,array($pk));
    }
    $usedFields = array_intersect($fields,$AllFields);

    $fb->fieldsToRender = $fields;

    $do->selectAdd();
    $plugins = $do->_getPluginsDef();
    if(is_array($plugins['i18n'])) {
      $tmparr = array_intersect($fields,$plugins['i18n']);
      $i18nfields = implode(','.$do->tablename().'_i18n'.'.',$tmparr);
      if(!empty($i18nfields)) {
        $i18nfields = $do->tablename().'_i18n'.'.'.$i18nfields;
      }
      $fieldsToRender = array_merge($usedFields,$tmparr);
      unset($tmparr);
    } elseif(is_array($plugins['l10n'])) {
      $tmparr = array_intersect($fields,$plugins['l10n']);
      $i18nfields = implode(','.$do->tablename().'_l10n'.'.',$tmparr);
      $i18nfields = $do->tablename().'_l10n'.'.'.$i18nfields;
      $fieldsToRender = array_merge($usedFields,$tmparr);
      unset($tmparr);
    } else {
      $i18nfields = null;
      $fieldsToRender = $usedFields;
    }

    if($plugins['tag'] && in_array('tagplugin_cache',$AllFields)) {
      $usedFields[]='tagplugin_cache';
    }
    if(!in_array($pk,$usedFields)) {
      $selectAdd = $do->tablename().'.'.$pk.','.$do->tablename().'.'.implode(','.$do->tablename().'.',$usedFields).($i18nfields?','.$i18nfields:'');
    } else {
      $selectAdd = $do->tablename().'.'.implode(','.$do->tablename().'.',$usedFields).($i18nfields?','.$i18nfields:'');
    }

    $do->selectAdd('distinct '.$selectAdd);
    $fb->populateOptions();
    $specialElements = $fb->_getSpecialElementNames();
    $eltTypes=$do->table();

    foreach($fieldsToRender as $aField) {
      switch(true) {
        case array_key_exists($aField,$links):
          $fieldTypes[$aField] = 'link';
        break;
        case is_array($fb->enumFields) && in_array($aField,$fb->enumFields):
          $fieldTypes[$aField] = 'enum';
        break;
        case $eltTypes[$aField] & DB_DATAOBJECT_BOOL:
          $fieldTypes[$aField] = 'bool';
        break;
        case $eltTypes[$aField] & DB_DATAOBJECT_TIME:
          $fieldTypes[$aField] = 'datetime';
        break;
        case $eltTypes[$aField] & DB_DATAOBJECT_DATE:
          $fieldTypes[$aField] = 'date';
        break;
        default:
        $fieldTypes[$aField] = 'bypass';
      }
    }
    foreach($columns as $field) {
      $columnsTypes[$field] = $fieldTypes[$field];
    }
    $this->columns = $columnsTypes;
    if(can('edit', $module) || can('view', $module)) {
        Mreg::get('tpl')->assign('edit',true);
    }
    if($this->_controller->hasActions) {
        Mreg::get('tpl')->assign('selectable',true);
        Mreg::get('tpl')->addJSinline(
        "
        $('a[rel=checkboxes]').click(function() {
            \$('#showTableForm input:checkbox').each(function(){\$(this).prop('checked',true);});
            return false;
        });
        $('a[rel=uncheckboxes]').click(function() {
            $('#showTableForm input:checkbox').prop('checked',false);
        });
        ",'ready');

    }
    Mreg::get('tpl')
      ->concat('adminTitle',' '.'page '.($_REQUEST['pageID']?$_REQUEST['pageID']:1));

    if (isset($_REQUEST['_ps'])) {
        $do->orderBy();
        $do->getPlugin('pager')->setValues($_REQUEST['_ps'],$_REQUEST['_pd']);

    } elseif ($ord = $this->_controller->moduloptions['order']) {
        $do->orderBy();
        $do->getPlugin('pager')->setDefaultSort($ord);
    } elseif (isset($fb->linkOrderFields) && is_array($fb->linkOrderFields)) {
        $do->getPlugin('pager')->setDefaultSort($fb->linkOrderFields);
    }
    $this->fields = $fieldTypes;

    $do->find();
    $this->do=$do;
    $this->totalItems = $do->getPlugin('pager')->totalItems;
  }
}
