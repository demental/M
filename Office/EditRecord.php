<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Record editing handling
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_EditRecord extends M_Office_Controller {

    public function __construct($module, $record) {
      
      $opts = PEAR::getStaticProperty('m_office','options');
      $this->module = $module;
      $this->moduloptions = $opts['modules'][$module];
      $table = $this->table = $this->moduloptions['table'];      
      $this->do = $this->getRecord($module, $record);
      parent::__construct();

    }

    public function getRecord($module, $record)
    {
      if($record instanceOf DB_DataObject) return $record;
      $do = M_Office_Util::doForModule($module,false);

      $keys = $do->keys();
      $do->{$keys[0]} = $record;
      if(!$do->find(true)) {
        $this->assign('__action','error');
        $this->append('errors',__('L\'enregistrement que vous avez tenté d\'atteindre est introuvable.'));
        return false;
        
      }

      return $do;
    }
    public function run()
    {
        $this->assign('__action','edit');
        $this->append('subActions','<a href="'.M_Office_Util::getQueryParams(array(), array('record','doSingleAction')).'">'.__('&lt; Back to list').'</a>');
        $editopts = PEAR::getStaticProperty('m_office_editrecord','options');
        if(!empty($editopts['tableOptions'][$this->module]['fields'])) {
          $this->do->fb_fieldsToRender = $editopts['tableOptions'][$this->module]['fields'];
        }
        $tpl = Mreg::get('tpl');
        $tpl->concat('adminTitle',$this->do->__toString().' :: '.$this->moduloptions['title']);

        $database = $this->do->database();

        /**
        *
        * Actions
        *
        **/
        if (isset($_REQUEST['doSingleAction']) && $this->getGlobalOption('actions','showtable',$this->module)) {
            require 'M/Office/Actions.php';
            $subController = new M_Office_Actions($this->getOptions());
            $subController->run($this->do, $_REQUEST['doSingleAction'],'single');
            if($subController->hasOutput()) {
        	    return;
        	}
    	}
        $this->createActions();

        if((!$this->getOption('directEdit',$this->module) && !isset($_REQUEST['editmode'])) || !$this->getOption('edit',$this->module)){
            $this->do->fb_userEditableFields=array('__fakefield');
        }

        $formBuilder =& MyFB::create($this->do);
        $form = new MyQuickForm('editRecord', 'POST', M_Office_Util::getQueryParams(array(), array('editmode'), false), '_self', null, true);
        Mtpl::addJS('jquery.form');

        Mtpl::addJsinline('
        var mdForm="";
        var formInSave="";
        function verifFormModified(form) {
            if(formInSave==true) return;
            var verif = Form.serialize(form);
            if(verif == mdForm) return;
            return "Vous avez effectué des modifications sur le formulaire qui seront annulées.";
        }
        ','ready');
        
        Mtpl::addJSinline("$('#editRecord').formSerialize();",'ready');
        Mtpl::addJSinline("$('#editRecord').submit(function() {
            formInSave=true;
        })
        ",'ready');
        Mtpl::addJSinline("return verifFormModified('editRecord');",'beforeunload');

        $formBuilder->elementTypeAttributes = array('longtext' => array('cols' => 50, 'rows' => 10));
        $formBuilder->useForm($form);
        if($this->getOption('edit',$this->module)){
            if((!$this->getOption('directEdit',$this->module) && !isset($_REQUEST['editmode']))){
                $form->addElement(MyQuickForm::createElement('header','modifHeader','<input type="button" onclick="top.location.href=\''.M_Office_Util::getQueryParams(array('editmode'=>1)).'\'"value="Modifier cet enregistrement"/>'));
                $doFreeze = true;
            } else {

                $this->assign('editable',true);
                $form->addElement(MyQuickForm::createElement('header','modifHeader','Modification activée'));
                $form->addElement('hidden','editmode',1);
                $form->addElement(MyQuickForm::createElement('checkbox','__backtolist__','Retourner à la liste après les modifications',''));
//                                        $form->setDefaults(array('__backtolist__'=>1));
            }
        } else {
          $doFreeze = true;
        }
        $form = $formBuilder->getForm();
        if (PEAR::isError($form)) {
            die($form->getMessage().' '.print_r($form->getUserInfo(), true));
        }
        if($doFreeze) {
          $form->freeze();
          $submit = $form->getElement('__submit__');
          if(!PEAR::isError($submit)) {
            $form->removeElement('__submit__');
          }
        }

        if ($form->validate()) {
          if (PEAR::isError($ret = $form->process(array(&$formBuilder, 'processForm'), false))) {
            $this->append('errors',__('An error occured while updating record').' : '.$ret->getMessage());
            $this->assign('__action','error');
            return;
          } else {
            $values=$form->exportValues();
            $remove[]='editmode';
            if($values['__backtolist__']){$remove[]='record';}
            if(!key_exists('debug',$_REQUEST)){
              $this->say('Record saved !');                      		    
                M_Office_Util::refresh(M_Office_Util::getQueryParams(array(), $remove, false));
            }
          }
        }
        $this->assign('editForm',$form);

        if ($linkFromTables = $this->getOption('linkFromTables', $this->table)) {
            $ajaxFrom = $this->getOption('ajaxLinksFromTable',$this->table);
            if(!is_array($ajaxFrom)) {
                $ajaxFrom = array();
            }
            foreach ($this->do->reverseLinks() as $linkFromTable => $field) {

                list($linkTab, $linkField) = explode(':', $linkFromTable);

                switch(true) {
                  case !$this->getGlobalOption('view','showtable',$linkTab): break;
                  case key_exists($linkTab,$ajaxFrom):
                    $fromfield = $ajaxFrom[$linkFromTable]['fromfield'];
                    if($fromfield==$linkField || !$fromfield) {
                        $info = $ajaxFrom[$linkTab];

                        require_once 'M/Office/ajaxFromTable.php';
                        $aja = new M_Office_ajaxFromTable($this->do, $this->module, $linkTab, $linkField, $this->do->$field);
                        if($info['position']=='before') {
                            $ajaxLinksBefore[]=$aja->getBlock();
                        } else {
                            $ajaxLinksAfter[]=$aja->getBlock();
                        }
                    }
                    break;
                  case $linkFromTables===TRUE || (is_array($linkFromTables) && in_array($linkTab,$linkFromTables)):
                    $linkFromTableArray[] = $this->getLinkFromTableItem($linkTab, $linkField, $field);
                    break;
                }
              }
            }  
            M::hook($this->do->tableName(),'alterLinkFromTables',array(&$linkFromTableArray,$this->do));

    $this->assign('linkFromTables',$linkFromTableArray);
    $this->assign('ajaxFrom',array('before'=>$ajaxLinksBefore,'after'=>$ajaxLinksAfter));
    $related='';
      if (($linkToTables = $this->getOption('linkToTables', $this->table)) && is_array($links = $this->do->links())) {
          foreach ($links as $linkField=>$link) {

            list($linkTab, $linkRec) = explode(':', $link);
            if ((!is_array($linkToTables) || in_array($linkTab, $linkToTables)) && $this->getOption('view',$linkTab)) {
                                  $this->append('linkToTables',array('table'=>$linkTab,
                                                                      'link'=>M_Office_Util::getQueryParams(array('module' => $linkTab,'record'=>$this->do->$linkField),array('page'))
                                                                    )
                                                );
            }
          }
    }
    $this->assign('related',$related);
    $this->assign('do',$this->do);
  }
  function createActions() {
    $singleMethods = M_Office_Util::getActionsFor($this->do,$this->module);
    foreach($singleMethods as $k=>$v){
      $this->append('relatedaction',array('url'=>M_Office_Util::getQueryParams(array("doSingleAction"=>$k)),'title'=>$v['title']));
    }
    return $singleMethods;    
  }  
  public function getLinkFromTableItem($linkTab, $linkField, $field)
  {            
    $linkDo=& DB_DataObject::factory($linkTab);
    if($nfield = $linkDo->isNtable()) {
      $nFields = $linkDo->links();
      $ntableArray = explode(':',$nFields[$nfield]);
      $nDo = DB_DataObject::factory($ntableArray[0]);
      $linkDo->$linkField=$this->do->$field;
      $nDo->joinAdd($linkDo);

      $nbLinkedRecords=$nDo->count();
      $parameters=array(  'module' => $nDo->tableName(),
                          'filternField' => $linkField,
                          'filternTable' => $linkTab,                                                              
                          'filternValue' => $this->do->$field);

                          $removed=array('module','filternValue','filternField');
      $add = false;         
      $tableName = M_Office_Util::getFrontTableName($ntableArray[0].' <small>(n-n)</small>');
    } else {
      $linkDo->$linkField=$this->do->$field;
      $nbLinkedRecords=$linkDo->count();
      $parameters=array(  'module' => $linkTab,
                          'filterField' => $linkField,
                          'filterValue' => $this->do->$field);

                          $removed=array('module','filterValue','filterField');
    
      if($nbLinkedRecords==1){
          $keys=$linkDo->keys();
          $key=$keys[0];
          $linkDo->selectAdd();
          $linkDo->selectAdd($linkDo->pkName());
          $linkDo->find(true);
          $parameters['record']=$linkDo->$key;
          $removed[]='record';
      }
      $tableName = M_Office_Util::getFrontTableName($linkTab);
      $add = $this->getGlobalOption('add','showtable', $linkTab)?true:false;
    }
    return array( 'table'=>$linkTab,
                  'linkField'=>$linkField,
                  'field'=>$field,
                  'link'=>M_Office_Util::getQueryParams($parameters,array_diff(array_keys($_GET),$removed)),
                  'nb'=>$nbLinkedRecords,
                  'tablename'=>$tableName,
                  'add'=>$add
                ); 

  }          
}