<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Actions handling.
* An action is triggered by the user upon database records in "db" type office modules.
* There are three ways to apply actions to records :
* - global : this just launches a DB_DataObjects_* method as a static method
* - batch : applies an action to user-selected records
* - single : applies an action to one record
* @todo
* - Security leak : check ACL for actions list. Currently this is done during the output of actions list, but not here which is a security leak.
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_Actions extends M_Office_Controller {

  protected $_selected;

  public $__start;
  /**
   * @param $options array of options
   */
	public function __construct($options) {
		parent::__construct($options);
		$this->has_output=false;
    $this->nextactions = explode(',',$_REQUEST['__actionchain']);

    if(empty($this->nextactions[0]) && !empty($_REQUEST['__nextaction'])) {
      $this->nextactions = array($_REQUEST['__nextaction']);
    }
    if(empty($this->nextactions[0])) {
      $this->nextactions = array();
    }
    $this->target = count($this->nextactions)>0?'nextaction':$_REQUEST['__target'];
		$this->__start = $_REQUEST['__start']<1?0:$_REQUEST['__start'];
    $this->__step = null;
  }

  /**
   * Runs the action
   * @param DB_DataObject on which to apply the action
   * @param string name of the DO method
   * @param string type of action (global,batch or single)
   */
  public function run($do,$actionName,$type='batch')
  {
	  $this->actionName = $actionName;
    $this->type=$type;
    switch($this->type) {
      case 'global':
      $this->typeval = 'glaction';
      break;
      case 'batch':
      $this->typeval = 'doaction';
      break;
      case 'single':
      $this->typeval = 'doSingleAction';
      break;
    }

    $this->do = $this->actiondo = $do;

    $this->do->selectAdd();
    $this->do->selectAdd($this->do->getDatabaseConnection()->quoteIdentifier($this->do->tableName()).'.*');
    $this->scope = $_REQUEST['__actionscope']=='all'?'all':'selected';
    if($actionName == 'delete') {
			if($this->getOption('delete', $table)){
	  			require_once('M/Office/DeleteRecords.php');
	  			$subController = new M_Office_DeleteRecords($this->do, $this->getSelectedIds());
			}
		  return;
	  }

    if(!$this->fillActionInfo($actionName,$type)) {
      $this->say(__('action %s not found !',array($actionName)));
      require_once 'M/Office/Actionresult.php';
      $result = new M_Office_Actionresult($this);
      $result->status='error';
      $this->redirectTo($result);
    }

    if($_REQUEST['__submitnext__']) {
      require_once 'M/Office/Actionresult.php';
      $result = new M_Office_Actionresult($this);
      if($_REQUEST['__nextaction'] || count($this->nextactions)>0) {
        $this->target = 'nextaction';
        $result->status='complete';
      } else {
        $result->status='complete';
        $this->target = 'list';
      }
      $this->redirectTo($result);
    }
    $form = $this->createParamsForm();
    if($this->isValid($form)) {
      $result = $this->applyActionWithParamsTo($this->actionName,$this->getParams($form),$this->type=='single'?$this->do:$this->getSelected(false));
      $this->redirectTo($result);
    } else {
			$this->assign('actionform',$form);
			$this->has_output=true;

      $this->assign('__action',array('action_'.$this->actionName,'action'));
      $this->assign('isdownload',$this->_actionInfo['isdownload']);
    }
  }
  /**
   * Applies the action to the passed collection
   * @param string name of the method to apply
   * @param mixed parameters to pass to the action method
   * @param DB_DataObject collection on which the action is applied (query ready, not executed)
   * @return M_Office_Actionresult containing the status of the action after applying it
   */
   public function applyActionWithParamsTo($actionName,$params,$focus)
   {
     require_once 'M/Office/Actionresult.php';
     $applyto = $this->getCurrentPartial($focus);
     $result = new M_Office_Actionresult($this);
     $result->params = $params;
	   if('batch'==$this->type) {
       $result->setSelected($focus);
       $result->setApplyto($applyto);
     }

     if($this->actiondo instanceOf M_Plugin) {
       $res = call_user_func(array($this->actiondo,$actionName),$applyto,$params);
		 } else {
       if(count($params)>0) {
          $res = call_user_func(array($applyto,$actionName),$params);
        } else {
          $res = call_user_func(array($applyto,$actionName));
        }
		 }
		 if($res === false) {
		   $result->status = 'error';
		 } else {
		   if('batch'==$this->type) {
         $result->processBatchStatus();
		   } else {
	       $result->status='complete';
		   }
		 }
		 return $result;
   }
   /**
    * Returns the stored parameters for the action.
    * If no action required, returns an empty array
    * @param mixed HTML_QuickForm or null if no params required for this action.
    * @return array if params
    * @return array empty array if no params
    */
   public function getParams($form)
   {
     if(is_a($form,'HTML_QuickForm')) {
       $data = $form->exportValues();
       if(is_array($_FILES) && count($_FILES)>1) {
         $data = array_merge($data,$_FILES);
       }
       unset($data['selected']);
       unset($data['__submit__']);
       unset($data[$this->typeval]);
       return $data;
     }
     return array();
   }
   /**
    * returns the current portion of the collection on which to apply the action
    * in case of multi-page action (otherwise returns the passed param with query executed)
    * @param DB_DataObject the complete collection with query not executed
    * @return DB_DataObject the partial collection with query executed
    */
   public function getCurrentPartial($focus)
   {

     switch($this->type) {
       // Single and global methods = no partial, we send back the whole resultset
       case 'single':
       case 'global':
        return $focus;
        break;
       // Batch : if the stepmethod exists, we apply a limit and execute the query to a clone of $this->do
       case 'batch':
        $res = clone($focus);
        if(is_array($stepinfo = $this->getStepInfo())) {
          list($step,$timeout) = $stepinfo;
          $this->__step = $step;
          $res->limit($this->__start,$this->__step);
        }
        $res->find();
        return $res;
        break;
     }
   }
  /**
   * Returns a DB_DataObjects collection, containing the tuples on which to apply the action
   * @param bool (default true) wether to query or just prepare the query (giving the possibility to add further clause)
   */
  public function getSelected($doQuery = true)
  {
    if($this->type=='single') return $this->do;
    if(!$this->_selected || !$doQuery) {
      switch($this->scope) {
        case 'all':
          $selected = clone($this->do);
          $selected->selectAdd();
          $selected->selectAdd($this->do->tableName().'.*');

        break;
        case 'selected':
          $db = $this->do->getDatabaseConnection();
          $ids = $this->getSelectedIds();
          array_walk($ids,array('M_Office_Util','arrayquote'),$db);
          $selected = M_Office_Util::doForTable($this->do->tableName());
          $selected->whereAdd(
            $db->quoteIdentifier($this->do->pkName()).' IN ('.
            implode(',',$ids).')'
          );
          break;
        }
      if($doQuery && !$selected->N) {
        $selected->find();
      }
    }
    if($doQuery) {
      $this->_selected = $selected;
    } else {
      return $selected;
    }
    return $this->_selected;
  }
  /**
   * Returns an indexed array of primary keys containing all the selected elements.
   * @return array
   */
   public function getSelectedIds()
   {
     switch($this->type) {
       case 'global':
         return array();
         break;
       case 'single':
         return array($this->do->pk());
         break;
       case 'batch':
         if('all'==$this->scope) {
           $all = clone($this->do);
           $arr = array();
           while($all->fetch()) {
             $arr[] = $all->pk();
           }
         } else {
           return $_REQUEST['selected'];// TODO abstract this
         }
         return $arr;
         break;
     }
   }
  /**
   * Redirects to the next page depending on $result status
   * @param $result M_Office_Actionresult
   */
  public function redirectTo(M_Office_Actionresult $result)
  {
    $toRemoveParams = array('__actionscope','doSingleAction','glaction','doaction','__start','__actionchain');
    switch($result->status) {
      case 'error':
        $this->say(__('An error occured while applying action'));
        switch($this->target) {
          case 'list':
            M_Office_Util::refresh(M_Office::URL(array(),
              $toRemoveParams
            ));
          break;
          default:
            M_Office_Util::refresh(M_Office::URL(array(),
              $toRemoveParams
            ));
        }
      break;
      case 'complete':

        switch($this->target) {
          case 'list':
            $this->say(__('Action applied successfully'));
            M_Office_Util::refresh(M_Office::URL(array(),
              $toRemoveParams
            ));
          break;
          case 'nextaction':
            $next = array_shift($this->nextactions);
            M_Office_Util::postRedirect(M_Office::URL(array($this->typeval=>$next,'__actionchain'=>implode(',',$this->nextactions)),
              array('__start')
              ),array('__actionscope'=>$this->scope,'selected'=>$this->getSelectedIds()));
          break;
          default:
            $this->say(__('Action applied successfully'));
            M_Office_Util::refresh(M_Office::URL(array(),
              $toRemoveParams
            ));
        }

      break;
      case 'partial':
        $this->nextStep($result);
      break;
    }
  }
  /**
   * Return step-by-step info as an array, false if the method must be applied at once
   * @return mixed : array(step,timeout) or false;
   */
  public function getStepInfo()
  {
    $stepmethod = 'step'.$this->actionName;
    if(method_exists($this->do,$stepmethod)) {
      return call_user_func(array($this->do,$stepmethod));
    } else {
      return false;
    }
  }
  /**
   * In case of a multi-page action, redirects to the next step
   */
  public function nextStep($result)
  {
    list($step,$timeout) = $this->getStepInfo();
    M_Office_Util::postRedirect(
      M_Office::URL(array('__start'=>$result->next)),
      $_POST,
      array('actionstep_'.$this->actionName,'actionstep'),
      array('start'=>$result->next,'timeout'=>$timeout*1000,'total'=>$result->total,'actionName'=>$this->getActionTitle())
    );
  }

  /**
   * Creates the form for action parameters, if params are required for the current action
   * @return null if no param
   * @return HTML_QuickForm if params
   */
  public function createParamsForm()
  {
    $tpl = Mreg::get('tpl');
    $tpl->concat('adminTitle',' :: '.$this->getActionTitle());

    if($this->actiondo instanceOf M_Plugin ) {
      $tpl->addPath('M/DB/DataObject/Plugin/'.$this->actiondo->getFolderName().'/templates/','before');
    }

    $prepareMethod = 'prepare'.$this->actionName;
    if(method_exists($this->actiondo,$prepareMethod) || is_array($this->_actionInfo['chainable'])) {
			$qfAction= new HTML_QuickForm('actionparamsForm','POST',M_Office_Util::getQueryParams(array(),array('selected','doaction','glaction','doSingleAction'), false), '_self', null, true);
      Mreg::get('tpl')->addJSinline('$("input[type=text],textarea","form[name=actionparamsForm]").eq(0).focus()','ready');
      Mreg::get('tpl')->assign('do',$do);
			$qfAction->addElement('header','qfActionHeader',$this->getActionTitle());
			$qfAction->addElement('hidden',$this->typeval,$this->actionName);
			$qfAction->addElement('hidden','__actionscope',$this->scope);

      if($this->scope=='selected') {
        M_Office_Util::addHiddenField($qfAction, 'selected', $this->getSelectedIds());
      }
      $selectedDo = $this->getSelected(true);
      if('single'==$this->type) {
        $selectedDo->fetch();
      }
      if(method_exists($this->actiondo,$prepareMethod)) {
        if(is_a($this->actiondo,'M_Plugin')) {
          call_user_func(array($this->actiondo,$prepareMethod),$qfAction,$selectedDo);
        } else {
          call_user_func(array($selectedDo,$prepareMethod),$qfAction);
        }
      }
      if($this->_actionInfo['chainable'] && count($this->nextactions)==0) {
        $qfAction->addElement('select','__nextaction',__('Execute this action then ...'),
        array_merge(array(''=>''),$this->getNextActions())
        );
      }
      if(count($this->nextactions)>0) {
        $qfAction->addElement('hidden','__actionchain',implode(',',$this->nextactions));
      }
		  $qfAction->addElement('submit','__submit__',__('Execute'));
      return $qfAction;
    } else {
      return null;
    }
  }

  /**
   * Checks wether :
   * - the passed variable is not a HTML_QuickForm. Therefore it's valid
   * - the passed variable is an HTML_QuickForm, and it validates
   */
  public function isValid($form)
  {
    if(is_a($form,'HTML_QuickForm') && $form->validate()) {
     return true;
    } else {
      if(!is_a($form,'HTML_QuickForm')) {
        return true;
      }
    }
    return false;
  }
  /**
   * fills the local array $_actionInfo provided the location of the method
   * @param string name of the action (method)
   * @param string type of the action (batch,single or global)
   * @return bool : true if the action exists, false if not.
   */
  public function fillActionInfo()
  {
    if(!$this->_actionInfo) {
      $this->_actionInfo = $this->getActionInfo($this->actionName,$this->type);
    }
    if ($this->_actionInfo['plugin']) {
      $this->actiondo = $this->do->getPlugin($this->_actionInfo['plugin']);
    }

    return $this->_actionInfo;
  }
  public function getActionInfo($actionName,$type)
  {
    $method = 'get'.$type.'methods';
    $allInfo = $this->do->{$method}();
    if(!key_exists($actionName,$allInfo)) {
      $res = false;
    } else {
      $res = $allInfo[$actionName];
    }
    return $res;
  }
  /**
   * Returns human-readable action name
   * @return string
   */
  public function getActionTitle()
  {
    $arr = $this->fillActionInfo();
    return $arr['title'];
  }
  /**
   * Returns an array of action if the current one is chainable ('chain' key in the action info)
   * @return array
   */
  public function getNextActions()
  {

    if(!is_array($this->_actionInfo['chainable'])) {
      return array();
    }
    $out = array();
    foreach($this->_actionInfo['chainable'] as $anAction) {
      $info = $this->getActionInfo($anAction,$this->type);
      $out[$anAction] = $info['title'];
    }

    return $out;
  }
}