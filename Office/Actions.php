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
* TODO : documentation about actions  
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_Actions extends M_Office_Controller {
  /**
   * @param $options array of options
   * @param $do the DataObject recordset upon which the action will be applied
   * @param $type string default 'batch'(default) 'global' or 'single' : action type
   */
	public function __construct($options, $do,$type='batch') {
		parent::__construct($options);
    
        switch($type) {
            case 'batch':
                $action = $_REQUEST['doaction'];
                $listCallback = 'getBatchMethods';
                $typeval = 'doaction';
                break;
            case 'global':
                $action = $_REQUEST['glaction'];
                $listCallback = 'getGlobalMethods';
                $typeval = 'glaction';
                break;
            case 'single':
                $action = $_REQUEST['doSingleAction'];
                $listCallback = 'getSingleMethods';
                $typeval = 'doSingleAction';
                break;
            
        }
        $this->assign('__action',array('action_'.$action,'action'));
        $scope = $_REQUEST['__actionscope'];
        switch($scope) {
            case 'checked':
                $selected=array_flip($_REQUEST['selected']);
                break;
            case 'all':
                break;
        }
		switch($action){
			case 'delete':
				if($this->getOption('delete', $table)){
		  			require_once('M/Office/DeleteRecords.php');
		  			$subController = new M_Office_DeleteRecords($do, $selected);
				}
				break;
			default:

				$actions = call_user_func(array($do,$listCallback));
				if(key_exists($action,$actions)){
					$pk = DB_DataObject_FormBuilder::_getPrimaryKey($do);
					$aj="";
					$clause="";
					$preparemethod='prepare'.$action;
          $stepmethod = 'step'.$action;
					$obj = empty($actions[$action]['plugin'])?$do:$do->getPlugin($actions[$action]['plugin']);
          $tpl = Mreg::get('tpl');
          $tpl->concat('adminTitle',' :: '.$actions[$action]['title']);
          if(!empty($actions[$action]['plugin'])) {
            $tpl->addPath('M/DB/DataObject/Plugin/'.$do->getPlugin($actions[$action]['plugin'])->getFolderName().'/templates/','beforeUser');
          }
					if(method_exists($obj,$preparemethod)){
				    $qfAction=& new HTML_QuickForm('actionparamsForm','POST',M_Office_Util::getQueryParams(array(),array('selected','doaction','glaction','doSingleAction'), false), '_self', null, true);
            Mreg::get('tpl')->addJSinline('$("input[type=text],textarea","form[name=actionparamsForm]").eq(0).focus()','ready');
            Mreg::get('tpl')->assignRef('do',$do);
						$qfAction->addElement('header','qfActionHeader',$actions[$action]['title']);
						$qfAction->addElement('hidden',$typeval,$action);
						if($typeval=='doaction') {


  						$qfAction->addelement('hidden','__actionscope',$scope);
              $clause='';
              if($scope=='checked') {
                $db = $do->getDatabaseConnection();
  						  foreach($selected as $id=>$v){
    							$qfAction->addElement('hidden','selected['.$v.']',$id);
  							  $clause.=$aj.$db->quoteIdentifier($do->tableName()).'.'.$db->quoteIdentifier($pk).' = '.$db->quote($id);
  							  $aj=" OR ";
  						  }
                $do->whereAdd($clause);
              }
              $obj2 = clone($do);
						  if((empty($clause) && $scope=='checked') || !$do->find()) {
						    $this->say('Aucun élément sélectionné. Aucune action n\'a été effectuée');
                M_Office_Util::clearRequest($values);
                if(!$_REQUEST['debug']) {
                  M_Office_Util::refresh(M_Office_Util::getQueryParams());
                }
                return;
					    }
                $start = $_REQUEST['__start']?$_REQUEST['__start']:0;

                if(method_exists($obj2,$stepmethod) && list($step,$timeout) = call_user_func(array($obj2,$stepmethod))) {
                  $objc = clone($obj2);
                  $count = $objc->count();
                  if($count>$step) {
                    $obj2->limit($start,$step);
                    $start+=$step;
                    if(!$obj2->find()) {
      						    $this->say('Action par lot terminée');
                      M_Office_Util::clearRequest($values);
                      if(!$_REQUEST['debug']) {
                        M_Office_Util::refresh(M_Office_Util::getQueryParams());
                      }
                      return;
      					    }
                  } else {
                    $obj2->find();
                    $step=false;
                  }
                } else {
                  $obj2->find();
                }

						} else {
						  $obj2 = $obj;
						}
            // Calling the 'prepareActionName()' method to populaite the form
            // If $do and $obj are not identical, it's a plugin call, so we pass the $do as well
            call_user_func(array($obj,$preparemethod),$qfAction,$do === $obj?null:$do);

				$qfAction->addElement('submit','__submit__','Valider');
				if($qfAction->isSubmitted() && $qfAction->validate()){

					$values=$qfAction->exportValues();
                    foreach ($qfAction->_elements as $key=>$elt) {
                        if($elt->_type=='file') {
                            $values[$elt->getAttribute('name')]=$elt->getValue();
                        }
                    }
			        unset($values['doaction']);
			        unset($values['glaction']);
			        unset($values['doSingleAction']);
                    unset($values['__actionscope']);

					$params="";
					$aj="";
      
					if(is_subclass_of($obj,'DB_DataObject_Plugin')) {
                        $res = call_user_func(array($obj,$action),$do,$values);
					} else {
                        $res = call_user_func(array($obj2,$action),$values);
					}
							

              if(false !== $res) {

                  if($step) {
                    $this->assign('__action',array('actionstep_'.$action,'actionstep'));                    
        				    $qfActionStep=& new HTML_QuickForm('actionparamsForm','POST',M_Office_Util::getQueryParams(array(),array('selected','doaction','glaction','doSingleAction'), false), '_self', null, true);
                    $stepValues = $qfAction->exportValues();
                    foreach($stepValues as $k=>$v) {
                      if($k=='__start') {
                        continue;
                      }
                      if(is_array($v)) {
                        foreach($v as $sk=>$sv) {
                          $qfActionStep->addElement('hidden',$k.'['.$sk.']',$sv);
                        }
                      } else {
                        $qfActionStep->addElement('hidden',$k,$v);
                      }
                    }
                    $this->assign('step',$step);
      							$this->assignRef('stepform',$qfActionStep);
      							$this->assign('start',min($start,$count));
                    $this->assign('total',$count);
                    $this->assign('actionName',$actions[$action]['title']);
                    $this->assign('timeout',$timeout*1000);
      							$this->has_output=true;
                    return;
                  }
		              $this->say(__('Action was executed successfully.'));
              } else {
                  $this->say(__('ERROR. No action executed.'));
              }
              M_Office_Util::clearRequest(array_merge($qfAction->getSubmitValues(), array('doaction'=>'','glaction'=>'','doSingleAction'=>'','selected'=>'','choice'=>'')));
              if(!$_REQUEST['debug']) {
                M_Office_Util::refresh(M_Office_Util::getQueryParams());
              }
						} else {

							$this->assignRef('actionform',$qfAction);
							$this->has_output=true;
						}
					} else {
					  $db = $do->getDatabaseConnection();
					    if($type == 'batch') {
                            if(!is_array($_REQUEST['selected'])) {
                                $_REQUEST['selected']=array();
                            }
						    foreach($_REQUEST['selected'] as $v=>$id){
							    $clause.=$aj.$pk.' = '.$db->quote($id);
							    $aj=" OR ";
						    }
						    unset($_REQUEST['selected']);
                            if(empty($clause)) {
							    $this->say(__('No record selected. Aucune action was executed'));
                                M_Office_Util::clearRequest($values);
                                if(!$_REQUEST['debug']) {
                                  M_Office_Util::refresh(M_Office_Util::getQueryParams());
                                }
                                return;                                
                            }
    						$do->whereAdd($clause);
                        }
						if('global' == $type || 'single' == $type || $do->find()){
							if(is_subclass_of($obj,'DB_DataObject_Plugin')) {
							    $values = array(&$do);
							} else {
							    $values = array();
							}
            $res = call_user_func_array(array($obj,$action),$values);

							if(false !== $res) {
						        $this->say('Action was executed successfully.');
                            } else {
                                $this->say(__('ERROR. No action executed.'));
                            }
                        }
                        M_Office_Util::clearRequest(array('doaction'=>'','glaction'=>'','doSingleAction'=>'','selected'=>'','choice'=>''));
                        if(!$_REQUEST['debug']) {
                          M_Office_Util::refresh(M_Office_Util::getQueryParams());
                        }
					}

				}
				  break;
			}
}		
}