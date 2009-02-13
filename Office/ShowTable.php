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
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Handles records listing/searching/paging/dispatching to actions
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_ShowTable extends M_Office_Controller {
  var $linkFields=array();
  var $hasActions=false;
  function M_Office_ShowTable($module) {
    M_Office_Controller::M_Office_Controller();
    if (isset($_REQUEST['record']) 
    && ($this->getOption('edit', $module) || $this->getOption('view', $module) || $this->getOption('directEdit', $module))) {
      require 'M/Office/EditRecord.php';
      $subController = new M_Office_EditRecord($module, $_REQUEST['record'],$additionalFilter);

      return;
    }

    $opts = PEAR::getStaticProperty('m_office','options');
    $this->module = $module;
    $this->moduloptions = $opts['modules'][$module];
    $this->table=$this->moduloptions['table'];
    if(!$this->getOption('view',$module)){
      M_Office_Util::refresh(ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT);
    }
    $do =& $this->doForTable($this->module);
    if($this->getOption('search',$module)){
        $doSearch =& $this->doForTable($this->module);
        $searchForm=& M_Office_Util::getSearchForm($doSearch);
        $this->assign('search',$searchForm);
        if (isset($_REQUEST['searchSubmit'])) {
          $do = $this->getSearchDO($searchForm);
        }            
    }
    if($this->getAndProcessActions(clone($do),$module)) {

        return;
    }

          
    if($this->getOption('view',$do->tableName())===TRUE) {
      require 'M/Office/View/DOPaging.php';
      $dg = &new M_Office_View_DOPaging($this);
      $this->assign('__listview','dopaging');
    } else {
      $classfile = 'M/Office/View/'.$this->getOption('view',$this->module).'.php';
      $class = 'M_Office_View_'.$this->getOption('view',$this->module);
      require $classfile;
      $dg = &new $class($this);
      $this->assign('__listview',$this->getOption('view',$this->module));
    }
    $tpl = Mreg::get('tpl');
    $tpl->concat('adminTitle',' :: '.$this->moduloptions['title'].' :: Listing');
    $pagination = $this->paginate===false?false:true;
    $dg->prepare($do,$this->module,$pagination);
    $this->assign('dg',$dg);
    $this->assign('total',$total);
    $this->assign('pager',$dg->getPaging());
    $this->assign('fields',$dg->getFields());
    $this->assign('__action','showtable');
    $deleteForm = new HTML_QuickForm('showTableForm', 'post', M_Office_Util::getQueryParams(array(),array(),false), '_self', null, true);
    M_Office_Util::addHiddenFields($deleteForm, array(), true); 
  }
    function &doForTable($table) {
      $do = M_Office_Util::doForModule($table);
      if($this->moduloptions['filters']) {
        foreach($this->moduloptions['filters'] as $filter) {
          if(is_array($filter)) {
            foreach($filter as $k=>$e) {
              $do->{$k} = $e;
            }
          } else {
      	    $do->whereAdd(preg_replace('`U::([a-zA-Z0-9_]+)`e',"User::getInstance('office')->getDBDO()->$1",$filter));
          }
        }
      }
      $db = $do->getDatabaseConnection();
      if (isset($_REQUEST['filterField']) && isset($_REQUEST['filterValue'])) {
        if(method_exists('quoteSmart',$db)) {
          $res = $db->quoteSmart($_REQUEST['filterValue']);
        } else {
          $res = $db->quote($_REQUEST['filterValue']);
        }

        $do->whereAdd($db->quoteIdentifier($_REQUEST['filterField']).' = '.$res);
        $filterString = __('%s for %s',array($do->tableName(),$_REQUEST['filterField'])).' = ';
        $links = $do->links();
        if (isset($links[$_REQUEST['filterField']])) {
          list($lTab, $lFld) = explode(':', $links[$_REQUEST['filterField']]);
          $linkedDo = DB_DataObject::factory($lTab);
          $linkedDo->get($lFld, $_REQUEST['filterValue']);
          $filterString .= '<a href="'.M_Office_Util::getQueryParams(array('module'=>$lTab,'record'=>$linkedDo->$lFld),array('filterValue','filterField')).'">'.MyFB::getDataObjectString($linkedDo).' ('.$_REQUEST['filterValue'].')</a>';
        } else {
          $filterString .= $_REQUEST['filterValue'].'<br/>';
        }
        $this->append('subActions',$filterString);
      }
      if($o = $do->getPlugin('ownership')) {
        if($o->userIsInAdminMode() && !empty($s[$do->ownerShipField])) {
          $do->whereAdd($do->tableName().'.'.$do->ownerShipField.' = '.$s[$do->ownerShipField]);
        }
      }
/*      if(is_array($do->fb_linkOrderFields)) {
        $do->orderBy(implode(',',$do->fb_linkOrderFields));
      }*/
    return $do;
   }
   function &getSearchDO($searchForm) {
     $do = $this->doForTable($this->module);
     $s=$searchForm->exportvalues();
     $this->paginate = !$s['__dontpaginate'];

     if(method_exists($do,'frontEndsearch')) {                
       foreach($s as $k=>$v){
         if(!key_exists($k,$do->table())){
           unset($s[$k]);
         }
       }
       $searchWhere=$do->frontEndsearch($s);
     } else {
       $searchWhere = '';
       $db = $do->getDatabaseConnection();
       foreach ($do->table() as $field => $type) {
         if (isset($_REQUEST[$field])) {
           if (is_string($_REQUEST[$field]) && $_REQUEST[$field] !== '') {
             if ($searchWhere) {
               $searchWhere .= ' AND ';
             }
             if(method_exists('quoteSmart',$db)) {
               $res = $db->quoteSmart('%'.$_REQUEST[$field].'%');
             } else {
               $res = $db->quote('%'.$_REQUEST[$field].'%');
             }
             $searchWhere .= $db->quoteIdentifier($do->tableName()).'.'.$db->quoteIdentifier($field).' LIKE '.$res;
           }
           //unset($_REQUEST[$field]);
           //unset($_POST[$field]);
           //unset($_GET[$field]);
         }
       }
     }  
     if ($searchWhere) {
       if(is_array($searchWhere)){
         foreach($searchWhere as $s){
           $do->whereAdd($s);	
         }
       } else {
         $do->whereAdd($searchWhere);
       }
     }
     $this->append('subActions','<a href="'.M_Office_Util::getQueryParams(array(), array_merge(array('searchSubmit', '__submit__'), array_keys($do->table()))).'">'.__('Reset search filter').'</a>');
     return $do;
   } 
   function getAndProcessActions(&$do,$table) {

     $batchActions=array();
     $globalActions=array();

     $do->find();
     $this->assign('globalActions',array());
     if($this->getOption('delete',$table)) {
       $batchActions['delete']=array('title'=>__('Delete'));
     }
     if($this->getOption('add',$table)) {
       $this->append('globalActions',array('url'=>M_Office_Util::getQueryParams(array('addRecord'=>1)),'title'=>'<strong>+</strong>','attributes'=>'class="addlink"'));
     }
     $acts = $this->getOption('actions',$table);

     if($acts){
       if(method_exists($do,'getBatchMethods')){
         $batchActions=$this->filterActions(array_merge($batchActions,$do->getBatchMethods()),$acts);
         
         $this->assign('batchActions',$batchActions);
       }
       if(method_exists($do,'getGlobalMethods')){
         $globalActions=$this->filterActions($do->getGlobalMethods(),$acts);
       }

       foreach($globalActions as $k=>$v){         
         $this->append('globalActions',array('url'=>M_Office_Util::getQueryParams(array('glaction'=>$k)),'title'=>$v['title']));
       }
     }
     if(count($batchActions)>0) {
       $this->hasActions=true;
     }


     if(isset($_REQUEST['addRecord']) && $this->getOption('add', $table)) {
       require 'M/Office/AddRecord.php';
       $subController = new M_Office_AddRecord($table);
       return true;
     }

     if (isset($_REQUEST['doaction']) && $this->getOption('actions',$table)) {
       require 'M/Office/Actions.php';
       $subController = new M_Office_Actions($this->getOptions(), $do);

       if($subController->has_output) {
   	     return true;
   	   }
     } elseif(isset($_REQUEST['glaction']) && $this->getOption('actions',$table)) {
        require 'M/Office/Actions.php';
        $result = new M_Office_Actions($this->getOptions(), $do,'global');
        if($result->has_output) {
     	    return true;
     	  }
     }
     return false;
   }
   function filterActions($arr,$actions) {
     if(!is_array($actions)) {
       return $arr;
     }
     foreach($arr as $action=>$data) {
       if(!in_array($action,$actions)) {
         unset($arr[$action]);
       }
     }
     return $arr;
   }
}