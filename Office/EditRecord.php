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
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_EditRecord extends M_Office_Controller {
    public function __construct($module, $record) {
      $opts = PEAR::getStaticProperty('m_office','options');
      $this->module = $module;
      $this->moduloptions = $opts['modules'][$module];
      $table = $this->table=$this->moduloptions['table'];
      
        parent::__construct();
        $this->assign('__action','edit');
        $do = M_Office_Util::doForModule($module,false);
        $keys = $do->keys();
        $do->{$keys[0]} = $record;
        if(!$do->find(true)) {
          $this->assign('__action','error');
          $this->append('errors',__('L\'enregistrement que vous avez tenté d\'atteindre est introuvable.'));
          return;
          
        }
        $this->append('subActions','<a href="'.M_Office_Util::getQueryParams(array(), array('record','doSingleAction')).'">'.__('&lt; Back to list').'</a>');
        $editopts = PEAR::getStaticProperty('m_office_editrecord','options');
        if(!empty($editopts['tableOptions'][$module]['fields'])) {
          $do->fb_fieldsToRender = $editopts['tableOptions'][$module]['fields'];
        }
        $tpl = Mreg::get('tpl');
        $tpl->concat('adminTitle',' :: '.$this->moduloptions['title'].' :: '.$do->__toString());

        $database = $do->database();

        /**
        *
        * Actions
        *
        **/
        if (isset($_REQUEST['doSingleAction']) && $this->getGlobalOption('actions','showtable',$table)) {
            require 'M/Office/Actions.php';
            $subController = new M_Office_Actions($this->getOptions());
            $subController->run($do, $_REQUEST['doSingleAction'],'single');
            if($subController->hasOutput()) {
        	    return;
        	}
    	}
        $this->createActions($do);

        if((!$this->getOption('directEdit',$module) && !isset($_REQUEST['editmode'])) || !$this->getOption('edit',$module)){
            $do->fb_userEditableFields=array('__fakefield');
        }

        $formBuilder =& MyFB::create($do);
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
        if($this->getOption('edit',$module)){
            if((!$this->getOption('directEdit',$module) && !isset($_REQUEST['editmode']))){
                $form->addElement(MyQuickForm::createElement('header','modifHeader','<input type="button" onclick="top.location.href=\''.M_Office_Util::getQueryParams(array('editmode'=>1)).'\'"value="Modifier cet enregistrement"/>'));
                $form->freeze();

            } else {
                    $form->addElement(MyQuickForm::createElement('header','modifHeader','Modification activée'));
                    $form->addElement('hidden','editmode',1);
                    $form->addElement(MyQuickForm::createElement('checkbox','__backtolist__','Retourner à la liste après les modifications',''));
//                                        $form->setDefaults(array('__backtolist__'=>1));
            }
        } else {
            $form->freeze();
        }
        $form =& $formBuilder->getForm();
        if (PEAR::isError($form)) {
            die($form->getMessage().' '.print_r($form->getUserInfo(), true));
        }
//        M_Office_Util::addHiddenFields($form);
                if ($form->validate()) {

                    if (PEAR::isError($ret = $form->process(array(&$formBuilder, 'processForm'), false))) {
                        $this->append('errors',__('An error occured while updating record').' : '.$ret->getMessage().'<br />'.$do->_lastError);
                        $this->assign('__action','error');
                        return;
                        } else {
                            $values=$form->exportValues();
                            $remove[]='editmode';
                            if($values['__backtolist__']){$remove[]='record';}
                            if(!key_exists('debug',$_REQUEST)){
                              $this->say('Vous pouvez maintenant travailler sur les données connexes');                      		    
                                M_Office_Util::refresh(M_Office_Util::getQueryParams(array(), $remove, false));
                            }
                        }
                    }
                    $this->assignRef('editForm',$form);

                    if ($linkFromTables = $this->getOption('linkFromTables', $table)) {
                        $linkTables = '';
                        $ajaxFrom = $this->getOption('ajaxLinksFromTable',$table);
                        if(!is_array($ajaxFrom)) {
                            $ajaxFrom = array();
                        }
                        foreach ($do->reverseLinks() as $linkFromTable => $field) {

                            list($linkTab, $linkField) = explode(':', $linkFromTable);

                            if ($this->getGlobalOption('view','showtable',$linkTab)) {

                                if(key_exists($linkTab,$ajaxFrom)) {

                                    $fromfield = $ajaxFrom[$linkFromTable]['fromfield'];
                                    if($fromfield==$linkField || !$fromfield) {
                                        $info = $ajaxFrom[$linkTab];

                                        require_once 'M/Office/ajaxFromTable.php';
                                        $aja = new M_Office_ajaxFromTable($linkTab,$linkField,$do->$field);
                                        if($info['position']=='before') {
                                            $ajaxLinksBefore[]=$aja->getBlock();
                                        } else {
                                            $ajaxLinksAfter[]=$aja->getBlock();
                                        }
                                    }
                                } else {
                                    if($linkFromTables===TRUE || (is_array($linkFromTables) && in_array($linkTab,$linkFromTables))) {

                                        $linkDo=& DB_DataObject::factory($linkTab);
                                        if($nfield = $linkDo->isNtable()) {
                                          // @todo : 
                                          $nFields = $linkDo->links();
                                          $ntableArray = explode(':',$nFields[$nfield]);
                                          $nDo = DB_DataObject::factory($ntableArray[0]);
                                          $linkDo->$linkField=$do->$field;
                                          $nDo->joinAdd($linkDo);

                                          $nbLinkedRecords=$nDo->count();
                                          $parameters=array(  'module' => $nDo->tableName(),
                                                              'filternField' => $linkField,
                                                              'filternTable' => $linkTab,                                                              
                                                              'filternValue' => $do->$field);

                                                              $removed=array('module','filternValue','filternField');
                                          $add = false;         
                                          $tableName = M_Office_Util::getFrontTableName($ntableArray[0].' <small>(n-n)</small>');
                                        } else {
                                          $linkDo->$linkField=$do->$field;
                                          $nbLinkedRecords=$linkDo->count();
                                          $parameters=array(  'module' => $linkTab,
                                                              'filterField' => $linkField,
                                                              'filterValue' => $do->$field);

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
                                        $this->append('linkFromTables',array('table'=>$linkTab,
                                                                             'linkField'=>$linkField,
                                                                             'field'=>$field,
                                                                             'link'=>M_Office_Util::getQueryParams($parameters,array_diff(array_keys($_GET),$removed)),
                                                                             'nb'=>$nbLinkedRecords,
                                                                             'tablename'=>$tableName,
                                                                             'add'=>$add
                                                                            )); 
                                    }
                                }
                            }
                        }
                    }

                $this->assign('ajaxFrom',array('before'=>$ajaxLinksBefore,'after'=>$ajaxLinksAfter));
                $related='';
            if (($linkToTables = $this->getOption('linkToTables', $table)) && is_array($links = $do->links())) {
                $linkTables = '';
                foreach ($links as $linkField=>$link) {

                  list($linkTab, $linkRec) = explode(':', $link);
                  if ((!is_array($linkToTables) || in_array($linkTab, $linkToTables)) && $this->getOption('view',$linkTab)) {
                                        $this->append('linkToTables',array('table'=>$linkTab,
                                                                            'link'=>M_Office_Util::getQueryParams(array('module' => $linkTab,'record'=>$do->$linkField),array('page'))
                                                                          )
                                                      );
                  }
                }
            }
            $this->assign('related',$related);
            $this->assignRef('do',$do);
        }
        function createActions($do) {
            $singleMethods=method_exists($do,'getSingleMethods')?$do->getSingleMethods():array();
            $opt = $this->getOption('actions', $do->tableName());

            if(is_array($opt)) {
                foreach($opt as $k=>$v) {
                  if(key_exists($v,$singleMethods)) {
                    $thS[$v] = $singleMethods[$v];
                  }
                }
                    $singleMethods = $thS;
            } elseif(!$opt) {
                    $singleMethods = array();
            }
            if(($_SESSION['adminLevel']==ADMINUSER || $_SESSION['adminLevel']==ROOTUSER) && key_exists('owner',$do->table())){
                $l = $do->links();
                $o = explode(':',$l['owner']);
                require_once 'HTML/QuickForm.php';
                $oDO = DB_DataObject::factory($o[0]);
                $oPK = DB_DataObject_FormBuilder::_getPrimaryKey($oDO);
                $oDO->find();
                while($oDO->fetch()) {
                    $oArr[$oDO->$oPK] = $oDO->__toString();
                }
                $userList = HTML_QuickForm::createElement('select','owner','Géré par',$oArr);

                if($o = $do->getLink('owner')) {
                $owner = $o->__toString();
                } else {
                    $owner='aucun';
                }
                $singleMethods['updateOwner']=array('title'=>'Changer le gérant(actuellement '.$owner.')','params'=>array('newowner'=>&$userList));

            }

            if(is_array($singleMethods)){
                foreach($singleMethods as $k=>$v){
                    $this->append('relatedaction',array('url'=>M_Office_Util::getQueryParams(array("doSingleAction"=>$k)),'title'=>$v['title']));
                }
            }
            return $singleMethods;    
    }      
}