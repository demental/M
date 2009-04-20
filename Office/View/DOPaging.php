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
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'Structures/DataGrid.php';
require_once 'M/Office/View/list.php';


class M_Office_View_DOPaging extends M_Office_View_List
{
    #   Constructor
    function M_Office_View_DOPaging ( &$controller )
    {
        M_Office_View_List::M_Office_View_List($controller);
    }
    ### 
    function getControllerOption($opt,$module = null) 
    {
        return $this->_controller->getOption($opt,$module);
    }
    function getPaging() {
        return $this->do->getPlugin('pager')->pager->getLinks();
    }
    function toHtml() {
        throw new Exception('toHTML not applicable with DOPaging');
    }
    function getTotalRecordsNum() {
      return $this->do->getPlugin('pager')->totalItems;
    }
    function setOptions($opts) {
      $this->pagerOptions = $opts;
    }
    function &prepare(&$do, $module, $pager = true) {
        $do->loadPlugin('pager');
        $f = array_keys($do->table());
        if($do->i18nFields) {
          $f = array_merge($f,$do->i18nFields);
        }
        $this->fieldNames=array_combine($f,$f);
        if(is_array($do->fb_fieldLabels)){
            foreach($do->fb_fieldLabels as $k=>&$v){
                if(is_array($v)){
                    if(key_exists('unit',$v)){
                        $unitFormatters[$k]=$v['unit'];
                    }
                    $v=$v[0];
                }
            }
            $this->fieldNames=array_merge($this->fieldNames,$do->fb_fieldLabels);
        }


        if ($pager){
            $perPage = $this->getControllerOption('recordsPerPage', $module);
            $perPage = $perPage === true ? 10 : $perPage;
        } else {
                $perPage = 100000000000;
        }
            $do->getPlugin('pager')->setOption('perPage',$perPage);

            $pk = MyFB::_getPrimaryKey($do);
            $AllFields=array_keys($do->table());
            $links = $do->links();
            $links=is_array($links)?$links:array();
            $LinkFields=array_keys($links);
            $noLinkFields=array_diff($AllFields,$LinkFields);
            $fields = $this->getControllerOption('fields', $module);
            if (!is_array($fields)) {
                $fields = array_diff($AllFields,array($pk));
            }
            $usedFields=array_merge($noLinkFields,array_intersect($fields,$LinkFields));
            while($offset = array_search($pk,$usedFields)){
                unset($usedFields[$offset]);
            }
            if($o=$do->getPlugin('ownership')) {
                if($o->userIsInAdminMode()) {
                    $fields=array_merge(array($do->ownerShipField),$fields);
                    $usedFields=array_merge(array($do->ownerShipField),$usedFields);
                    $do->fb_fieldLabels[$do->ownerShipField]=__('Owner');
                }
            }
            $do->fb_fieldsToRender=$fields;
            $fb =& MyFB::create($do);
            $do->selectAdd();
            if(is_array($do->i18nFields)) {
              $i18nfields = implode(','.$do->tablename().'_i18n'.'.',array_intersect($fields,$do->i18nFields));
            } else {
              $i18nfields = null;
            }
            if($i18nfields) {
              $i18nfields = $do->tablename().'_i18n'.'.'.$i18nfields;
            }
            if(!in_array($pk,$usedFields)) {
              $selectAdd = $do->tablename().'.'.$pk.','.$do->tablename().'.'.implode(','.$do->tablename().'.',$usedFields).($i18nfields?','.$i18nfields:'');
            } else {
              $selectAdd = $do->tablename().'.'.implode(','.$do->tablename().'.',$usedFields).($i18nfields?','.$i18nfields:'');
            }
            $do->selectAdd($selectAdd);
            $fb->populateOptions();
            $specialElements = $fb->_getSpecialElementNames();
            $eltTypes=$do->table();
            foreach($fields as $aField) {
              switch(true) {
                case in_array($aField,$LinkFields):
                $fieldTypes[$aField] = 'link';
                break;
                case is_array($do->fb_enumFields) && in_array($aField,$do->fb_enumFields):
                $fieldTypes[$aField] = 'enum';
                break;
                case $eltTypes[$aField] & DB_DATAOBJECT_BOOL:
                $fieldTypes[$aField] = 'bool';
                break;
                default:
                $fieldTypes[$aField] = 'bypass';
              }
            }
            if($this->getControllerOption('edit', $module) || $this->getControllerOption('directEdit', $module) || $this->getControllerOption('view', $module)) {
                Mreg::get('tpl')->assign('edit',true);
            }
            if($this->_controller->hasActions) {
                Mreg::get('tpl')->assign('selectable',true);
                Mreg::get('tpl')->addJSinline(
                "
                $('a[@rel=checkboxes]').click(function() {
                    \$('#showTableForm input:checkbox').each(function(){\$(this).attr('checked','checked');});
                    return false;
                });
                $('a[@rel=uncheckboxes]').click(function() {
                    $('#showTableForm input:checkbox').attr('checked','');
                });
                ",'ready');
              
            }
            $tpl = Mreg::get('tpl');
            $tpl->concat('adminTitle',' '.'page '.($_REQUEST['pageID']?$_REQUEST['pageID']:1));

            if (isset($_REQUEST['_ps'])) {
                $do->orderBy();
                $do->getPlugin('pager')->setValues($_REQUEST['_ps'],$_REQUEST['_pd']);

            } elseif ($ord = $this->_controller->moduloptions['order']) {
                $do->orderBy();
                $do->getPlugin('pager')->setDefaultSort($ord);
            } elseif (isset($do->fb_linkOrderFields) && is_array($do->fb_linkOrderFields)) {
                $do->getPlugin('pager')->setDefaultSort($do->fb_linkOrderFields);
            }
            $this->fields = $fieldTypes;
            $do->find();
            $this->do=$do;
            $this->totalItems = $do->getPlugin('pager')->totalItems;
          }

        }
?>