<?php

#doc
#   classname:  M_Office_View_DataGrid
#   scope:      PUBLIC
#
#/doc
require_once 'Structures/DataGrid.php';
require_once 'M/Office/View/list.php';


class M_Office_View_DataGrid extends M_Office_View_List
{
    #   Constructor
    function M_Office_View_DataGrid ( &$controller )
    {
        M_Office_View_List::M_Office_View_List($controller);
    }
    ### 
    function getControllerOption($opt,$table = null) 
    {
        return $this->_controller->getOption($opt,$table);
    }
    function getPaging() {
        return $this->_view->renderer->getPaging();
    }
    function toHtml() {
        return $this->_view->getOutput();
    }
    function setOptions($opts) {
        return $this->_view->renderer->setOptions($opts);
    }
    function &prepare(&$do, $frontend = true,$pager = true) {
        require_once 'M/Office/DataSource/MyDataObj2.php';
        Log::info('creation Datagrid');

        $unitFormatters=array();
        if(is_array($do->fb_fieldLabels)){
            foreach($do->fb_fieldLabels as $k=>&$v){
                if(is_array($v)){
                    if(key_exists('unit',$v)){
                        $unitFormatters[$k]=$v['unit'];
                    }
                    $v=$v[0];
                }
            }
        }


        if ($frontend && $pager){
            $perPage = $this->getControllerOption('recordsPerPage', $do->tableName());
            $perPage = $perPage === true ? 10 : $perPage;
            } else {
                $perPage = null;
            }


            $pk = MyFB::_getPrimaryKey($do);
            $AllFields=array_keys($do->table());
            $links = $do->links();
            $links=is_array($links)?$links:array();
            $LinkFields=array_keys($links);
            $noLinkFields=array_diff($AllFields,$LinkFields);
            $fields = $this->getControllerOption('fields', $do->tableName());
            if (!is_array($fields)) {
                $fields = array_diff($AllFields,array($pk));
            }
            $usedFields=array_merge($noLinkFields,array_intersect($fields,$LinkFields));
            while($offset = array_search($pk,$usedFields)){
                unset($usedFields[$offset]);
            }
            if($o=$do->getPlugin('ownership')) {
                Log::info('table filtrée par owner');
                if($o->userIsInAdminMode()) {
                    Log::info('ajout du champ owner');
                    $fields=array_merge(array($do->ownerShipField),$fields);
                    $usedFields=array_merge(array($do->ownerShipField),$usedFields);
                    $do->fb_fieldLabels[$do->ownerShipField]='Gérant';
                }
            }
            $do->fb_fieldsToRender=$usedFields;
            $fb =& MyFB::create($do);

            $do->selectAdd();
            if(!in_array($pk,$usedFields)) {
                $do->selectAdd($pk.','.implode(',',$usedFields));
                } else {
                    $do->selectAdd(implode(',',$usedFields));
                }
                $fb->populateOptions();
                $specialElements = $fb->_getSpecialElementNames();
                $dg =& new Structures_DataGrid($perPage);
                $dts= new Structures_DataGrid_DataSource_MyDataObj2();
                $dts->bind($do, array('generate_columns' => false,
                'fields'=>$usedFields,
                ));
                $dg->bindDataSource($dts);
                if($frontend){
                    if($this->getControllerOption('edit', $do->tableName()) || $this->getControllerOption('directEdit', $do->tableName()) || $this->getControllerOption('view', $do->tableName())) {
                        $dg->addColumn(new Structures_DataGrid_Column(null,null, null, array('width'=>'50px'), null,
                        array('M_Office_ShowTable','getEditLink'),array('database' => $do->database(), 'table' => $do->tableName(), 'pk' => $pk, 'directEdit' => $this->getControllerOption('view',$do->tableName()) & $this->getControllerOption('directEdit',$do->tableName()))));
                    }
                    if($this->_controller->hasActions) {
                        $dg->addColumn(new Structures_DataGrid_Column('',null, null, array('width'=>'20px'), null,
                        array('M_Office_ShowTable','getSelectedEntry'),array('pk' =>$pk)));
                    }
                    $formatters = $this->getControllerOption('formatters', $do->tableName());
                }
                if(!is_array($formatters)){
                    $formatters=array();
                }

                if (isset($do->fb_linkOrderFields) && is_array($do->fb_linkOrderFields)) {
                    $dg->setDefaultSort($do->fb_linkOrderFields);
                }

                $eltTypes=$do->table();
                $dateRenderer=$this->getControllerOption('dateRenderer');
                foreach ($fields as $field) {
                    if (!isset($specialElements[$field])) {
                        if(key_exists($field,$unitFormatters)) {
                            $dg->addColumn(new Structures_DataGrid_Column($fb->getFieldLabel($field), $field, in_array($field,$AllFields)?$field:null,null,null,key_exists($field,$formatters)?$formatters[$field]['callback']:array('M_Office_ShowTable','getFieldWithUnit'),key_exists($field,$formatters)?$formatters[$field]['args']:array('field' => $field, 'unit' => $unitFormatters[$field])));
                            }elseif(($eltTypes[$field] & DB_DATAOBJECT_DATE) && !empty($dateRenderer) && $dateRenderer!==true){

                                $dg->addColumn(new Structures_DataGrid_Column($fb->getFieldLabel($field), $field, in_array($field,$AllFields)?$field:null,null,null,key_exists($field,$formatters)?$formatters[$field]['callback']:$dateRenderer,key_exists($field,$formatters)?$formatters[$field]['args']:array('field' => $field)));
                                } else {
                                    $dg->addColumn(new Structures_DataGrid_Column($fb->getFieldLabel($field), $field, in_array($field,$AllFields)?$field:null,null,null,key_exists($field,$formatters)?$formatters[$field]['callback']:null,key_exists($field,$formatters)?$formatters[$field]['args']:null));
                                }
                            }
                        }
                        if (isset($_REQUEST['orderBy'])) {
                            $dg->sortRecordSet($_REQUEST['orderBy'],$_REQUEST['direction']);
                        }

                        require_once 'Structures/DataGrid/Renderer/HTMLTable.php';

                        $renderer=&new Structures_DataGrid_Renderer_HTMLTable();

                        $renderer->setTableAttribute('width', '100%');
                        $renderer->setTableAttribute('cellspacing', '0');
                        $renderer->setTableAttribute('cellpadding', '0');
                        $renderer->setTableAttribute('class', 'datagrid');
                        $renderer->setTableOddRowAttributes(array('class' => 'oddRow'));
                        $renderer->setTableEvenRowAttributes(array('class' => 'evenRow'));
                        $renderer->sortIconASC = '&uarr;';
                        $renderer->sortIconDESC = '&darr;';
                        $renderer->setOption('selfPath',ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT);

                        $dg->attachRenderer($renderer);

                        $this->_view=&$dg;
                    }


                }
                ###?>