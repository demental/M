<?php
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
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_ShowTable extends M_Office_Controller {
  var $linkFields=array();
  var $hasActions=false;
  function __construct($module) {
    parent::__construct();
    if ((isset($_REQUEST['record']) || isset($_REQUEST['__record_ref']))
    && ($this->getOption('edit', $module) || $this->getOption('view', $module) || $this->getOption('directEdit', $module))) {
      require 'M/Office/EditRecord.php';
      
      $subController = new M_Office_EditRecord($module, $_REQUEST['record'], $additionalFilter);
      $subController->__record_ref = $_REQUEST['__record_ref'];
      
      $subController->run();
      return;
    }
    if(isset($_REQUEST['addRecord']) && $this->getOption('add', $module)) {
      require 'M/Office/AddRecord.php';
      $subController = new M_Office_AddRecord($module);
      $subController->run();
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
      // 1. Url curation if needed
      if(!key_exists('_c_',$_REQUEST)) {
        M_Office_Util::refresh(M_Office::cleanURL(array('_c_'=>1),array('searchSubmit','__submit__')));
      }
      // 2. Process search
      $doSearch = $this->doForTable($this->module);
      $searchForm = M_Office_Util::getSearchForm($doSearch);
      $this->assign('search',$searchForm);
      $searchValues = $searchForm->exportValues();
    } else {
      $searchValues = array();
    }
    $do = $this->getSearchDO($searchValues);

    if (isset($_REQUEST['doaction']) && $this->getOption('actions',$module)) {
      require 'M/Office/Actions.php';
      $do->orderBy();
      $do->orderBy($_REQUEST['_ps'].' '.$_REQUEST['_pd']);

      $subController = new M_Office_Actions($this->getOptions());
      $subController->run($do, $_REQUEST['doaction'],'batch');
      if($subController->has_output) {
  	     return;
  	   }
    } elseif(isset($_REQUEST['glaction']) && $this->getOption('actions',$module)) {
       require 'M/Office/Actions.php';
       $subController = new M_Office_Actions($this->getOptions());
       $subController->run($do, $_REQUEST['glaction'],'global');
       if($subController->has_output) {
    	    return;
    	  }
    }
    
    if($this->getAndProcessActions(clone($do),$module)) {

        return;
    }

          
    if($this->getOption('view',$this->module)===TRUE) {
      require 'M/Office/View/DOPaging.php';
      $dg =  new M_Office_View_DOPaging($this);
      $this->assign('__listview','dopaging');
    } else {
      $classfile = 'M/Office/View/'.$this->getOption('view',$this->module).'.php';
      $class = 'M_Office_View_'.$this->getOption('view',$this->module);
      require $classfile;
      $dg =  new $class($this);
      $this->assign('__listview',$this->getOption('view',$this->module));
    }
    $tpl = Mreg::get('tpl');
    $tpl->concat('adminTitle',$this->moduloptions['title'].' :: Listing');
    $pagination = $this->paginate===false?false:true;
    $dg->prepare($do,$this->module,$pagination);
    $this->assign('dg',$dg);
    $this->assign('total',$total);
    $this->assign('pager',$dg->getPaging());
    $this->assign('fields',$dg->getFields());
    $this->assign('__action','showtable');
    $deleteForm = new HTML_QuickForm('showTableForm', 'post', M_Office_Util::getQueryParams(array(),array()), '_self', null, true);
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

        $do->whereAdd($db->quoteIdentifier($do->tableName()).'.'.$db->quoteIdentifier($_REQUEST['filterField']).' = '.$res);
        $filterString = __('%s for %s',array($do->tableName(),$_REQUEST['filterField'])).' = ';
        $links = $do->links();
        if (isset($links[$_REQUEST['filterField']])) {
          list($lTab, $lFld) = explode(':', $links[$_REQUEST['filterField']]);
          $linkedDo = DB_DataObject::factory($lTab);
          $linkedDo->get($lFld, $_REQUEST['filterValue']);
          $filterString .= '<a href="'.M_Office_Util::doURL($linkedDo, $lTab,array(),array('filterValue','filterField')).'">'.MyFB::getDataObjectString($linkedDo).' ('.$_REQUEST['filterValue'].')</a>';
        } else {
          $filterString .= $_REQUEST['filterValue'].'<br/>';
        }
        $this->append('subActions',$filterString);
      }
      if(isset($_REQUEST['filternTable']) && isset($_REQUEST['filternField']) && isset($_REQUEST['filternValue'])) {
        $joinDo = DB_DataObject::factory($_REQUEST['filternTable']);
        $joinDo->{$_REQUEST['filternField']} = $_REQUEST['filternValue'];
        $do->joinAdd($joinDo);
        $joinDOlinks = $joinDo->links();
        $targetTableArray = explode(':',$joinDOlinks[$_REQUEST['filternField']]);
        $targetDO = DB_DataObject::factory($targetTableArray[0]);
        $targetDO->get($_REQUEST['filternValue']);
        if(method_exists($targetDO,'__toString')) {
          $targetName = $targetDO->__toString();
        } else {
          $targetName = $targetDO->pk();
        }
        $filterString = __('%s linked to %s',array($do->tableName(),$targetTableArray[0])).' '.$targetName;        
        $this->append('subActions',$filterString);
        
      }
      /** Adding join objects if specified in the module configuration.
      * To add a join object you can :
      * - specify the foreign key against which the join will be added
      * - specify a foreign_table:foreign_field in cas of reverselink.
      * If only one table to join with, the parameter can be a string. 
      * If more than one table, write it as an array (see example below)
      * Example :
      * 'order'=>array(
      *  'type'=>'db',
      *  'title'=>'Orders',
      *  'table'=>'order',
      *  'join'=>array('customer_id','invoice:order_id')
      * )
      * Will create the following query :
      * SELECT order.* from order 
      * LEFT JOIN customer ON customer.id = order.customer_id
      * LEFT JOIN invoice ON invoice.order_id = order.id
      **/ 

      if($this->moduloptions['join']) {
        if(!is_array($this->moduloptions['join'])) {
          $j = array($this->moduloptions['join']);
        } else {
          $j = $this->moduloptions['join'];
        }
        $links = $do->links();
        $rlinks = $do->reverseLinks();
        $joindos = array();
        foreach($j as $ajoin) {
          if(strstr($this->moduloptions['join'],':')) {
            // Reverselink
            
          } else {
            // link
            if(!key_exists($ajoin,$links)) continue;
            $tfield = explode(':',$links[$ajoin]);
            $joindos[$ajoin] = DB_DataObject::factory($tfield[0]);
            $do->joinAdd($joindos[$ajoin]);
            $do->selectAs(null);
            $do->selectAs($joindos[$ajoin],$ajoin.'_%s');
          }
        }
      }
    return $do;
   }
   /**
    * Returns a DAO filtered with the search criterias specified by the searchForm
    * Uses $do->frontEndSearch to process form values if this method exists in the DAO
    * @param $searchForm array of values HTML_QuickForm search form after submission
    * @return DB_DataObject filtered dataObject  
    */
   function &getSearchDO($searchValues) {
     $do = $this->doForTable($this->module);
     $this->paginate = !$searchValues['__dontpaginate'];
     if(!$this->paginate) ini_set('memory_limit','512M');
     // Cleaning unused form fields
     unset($searchValues['__submit__']);
     unset($searchValues['__dontpaginate']);

     // Use of $do->frontendsearch() if the method exists
     if(method_exists($do,'frontEndsearch')) {                
       $do->frontEndsearch($searchValues);
       if(count($searchValues)==0) return $do;
     } else {
       if(count($searchValues)==0) return $do;
       // Guess query from field types if $do->frontendsearch() is not implemented
       $searchWhere = '';
       $db = $do->getDatabaseConnection();
       $fields = $do->table();
       foreach ($do->table() as $field => $type) {
         if (isset($searchValues[$field])) {
           if (is_string($searchValues[$field]) && $searchValues[$field] !== '') {
             if(key_exists($field,$do->links()) 
               // Foreign key, int or bool => search with field = value 
                || $fields[$field]&DB_DATAOBJECT_INT 
                || $fields[$field]&DB_DATAOBJECT_BOOL) {
                $do->$field = $_REQUEST[$field];
                
             } elseif($fields[$field]&DB_DATAOBJECT_DATE) {
               // Date => search by day
               $do->whereAdd('date('.$db->quoteIdentifier($do->tableName()).'.'.$db->quoteIdentifier($field).') = '.$db->quote($fields[$field]));

             } else {
               // Other (char, varchar)
               $res = $db->quote('%'.$searchValues[$field].'%');
               $do->whereAdd($db->quoteIdentifier($do->tableName()).'.'.$db->quoteIdentifier($field).' LIKE '.$res);

             }
          }
        }
      }
    }
     $this->append('subActions','<a href="'.M_Office_Util::getQueryParams(array(), array_merge(array('searchSubmit', '__submit__'), array_keys($do->table()))).'">'.__('Reset search filter').'</a>');
     return $do;
   } 
   function getAndProcessActions(&$do,$table) {

     $batchActions=array();
     $globalActions=array();

/*     $do->find();*/
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