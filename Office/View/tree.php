<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Tree view handler.
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'Structures/DataGrid.php';
require_once 'M/Office/View/list.php';

class M_Office_View_tree extends M_Office_View_List
{
    var $fields;
    var $_formatters;
    var $_do;
    var $_odd;
    #   Constructor
    function M_Office_View_DataGrid ( &$controller )
    {
        M_Office_View_List::M_Office_View_List($controller);
    }
    ###
    function getPaging() {
        return '';
    }
    function toHtml() {
        $left = $this->_do->treeFields['left'];
        $right = $this->_do->treeFields['right'];
        $father = $this->_do->treeFields['parent'];
        $rarr = array();
        $this->_do->find(true);
        $this->_odd='odd';
        return '<ul style="margin:0;padding:0;list-style-type:none;" id="sortablelist">'.$this->treePortion().'</ul>';
    }

    function treePortion($pid=null) {
        $fatherField = $this->_do->treeFields['parent'];
        $right = $this->_do->treeFields['right'];
        $left = $this->_do->treeFields['left'];
        $out='';
        $treeSize = ($this->_do->$right-$this->_do->$left)-1;
        if($treeSize == 0) {
           $treeSize = 1;
        }

        $out.='<div  class="'.$this->_odd.'Row" style="margin:0;padding:0;border-bottom:1px solid;height:2em;line-height:2em">';
        $first = true;
        foreach($this->fields as $field) {
            if($first) {
                $first = false;
                if(is_array($field)) {
                    $f=$this->_do->$field[0];
                } else {
                    $f=$this->_do->$field;
                }
            } else {
                $out.='<div style="float:right;width:100px;border-left:1px solid;">';
                if(is_array($field)) {
                    $out.=call_user_func_array($field[1],array(array('record'=>$this->_do->toArray()),$field[2]));
                } else {
                    $out.=$this->_do->$field;
                }
                $out.='</div>';
            }
        }
        if($treeSize!=1) {
            $pid = 'portion'.$this->_do->id;
            $treeStart=true;
                $foldlink='<a href="javascript:void(0)" rel ="treeviewtoggle">&darr;</a>';
        } else {
            $foldlink='';
        }
        $out.=$foldlink.$f.'</div><div style="clear:right">&nbsp;</div>';
        if($treeSize==1) {
            return '<li style="margin:0;padding:0;" id="'.$this->_do->id.'" class="sortableContainer'.$pid.'">'.$out.'</li>';
        }
        if($treeSize!=1) {
            $out.='<ul style="margin:0;padding:0;padding-left:1em;list-style-type:none;border-left:1px dotted" id="'.$pid.'">';
            $fout='</ul>';
        } else {
          $fout='';
        }
         while($treeSize>0) {
             $this->_odd=$this->_odd=='even'?'odd':'even';
            $this->_do->fetch();
            $treeSize-=($this->_do->$right-$this->_do->$left)+1;
            $out.=$this->treePortion($pid);
        }
        $out.=$fout;
        if($treeStart) {
            $out.='<script type="text/javascript">$(\'#'.$pid.'\').Sortable({
            				accept: \'sortableContainer'.$pid.'\',
            				  onChange : function(ser)
                      				{
                      				  serial = $.SortSerialize(\''.$pid.'\');
                      				  $.get(\''.M_Office_Util::getQueryParams(array('treesort'=>'1','parent'=>$pid),array(),false).'&\'+serial.hash);
                      				}})</script>';
        }
        return '<li id="'.$this->_do->id.'" class="sortableContainer'.$pid.'">'.$out.'</li>';
    }
    function setOptions($opts) {
        return;// $this->_view->renderer->setOptions($opts);
    }
    function getEditLink($params,$args=null) {
      return '<a href="'.
      M_Office_Util::getQueryParams(array('record' => $params['record'][$args['pk']])).
        '">'.__('Details').'</a>';
    }
    function getChildLink($params,$args=null) {
      return '<a href="'.
      M_Office_Util::getQueryParams(array('addRecord'=>1,'filterField'=>'parent_id','filterValue'=> $params['record'][$args['pk']])).
      '">+Enfant</a>';
    }
    function getSelectedEntry($params,$args) {
      return '<input type="checkbox" name="selected[]" value="'.$params['record'][$args['pk']].'" onclick="deleteCheckboxClicked(this)" style="border:none;clear:left"/>';
    }

    function &prepare(&$do, $module, $pager = true) {

        $this->_do = & $do;
        $do->orderBy($do->treeFields['left'].' ASC');
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

            $pk = MyFB::_getPrimaryKey($do);
            $fields = $this->getControllerOption('fields', $do->tableName());
            if (!is_array($fields)) {
                $fields = array_keys($do->table());
            }

            $do->fb_fieldsToRender=$fields;

            $fb =& MyFB::create($do);
            $fb->populateOptions();
            $specialElements = $fb->_getSpecialElementNames();
            $this->fields = $fields;



                    if($this->getControllerOption('edit', $do->tableName()) || ($this->getControllerOption('view', $do->tableName()))) {
                        $this->fields[]=array($pk,array('M_Office_View_tree','getEditLink'),array('database' => $do->database(), 'table' => $do->tableName(), 'pk' => $pk));
                        $this->fields[]=array($pk,array('M_Office_View_tree','getChildLink'),array('database' => $do->database(), 'table' => $do->tableName(), 'pk' => $pk));
                    }



                    if($this->_controller->hasActions) {
                        $this->fields[]=array($pk,array('M_Office_View_tree','getSelectedEntry'),array('pk' =>$pk));
                    }

                    $formatters = $this->getControllerOption('formatters', $do->tableName());
                if(!is_array($formatters)){
                    $formatters=array();
                }
                Mreg::get('tpl')->addJS('interface');
                Mreg::get('tpl')->addJSinline('    $("a[rel=treeviewtoggle]").toggle(
                                                function(){
                                                    $(this).parent().parent().find("ul").hide();
                                                },
                                                function(){
                                                    $(this).parent().parent().find("ul").show();
                                                }
                                                );','ready');

              }


                }
