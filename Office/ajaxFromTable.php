<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* class to handle ajax-based foreign records (e.g. order lines)
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_ajaxFromTable extends M_Office_Controller
{
    public $table;
    public $field;
    public $value;
    function M_Office_ajaxFromTable($linkTable,$linkField,$value) {

        M_Office_Controller::M_Office_Controller();
        $this->table = $linkTable;
        $this->module = $linkTable;
        $this->field = $linkField;
        $this->value = $value;
    }
    function getBlock() {
        $tpl = $this->tplInstance($this->module);
        Mreg::get('tpl')->addJS('office/fromtableHelpers');
        $add = $this->getGlobalOption('add','showtable',$this->table);
        $title = $this->getOption('title',$this->table);

        if(strlen($title)<2) {
            $title = $this->table;
        }
        $tpl->assign('add',$add);
        $tpl->assign('title',$title);
        if($add) {
          $tpl->assignRef('addform',$this->getAddFormObject());
        }
        $tpl->assign('table',$this->table);
        $tpl->assign('field',$this->field);
        $tpl->assign('value',$this->value);        
        $edit = $this->getGlobalOption('edit','showtable',$this->table);
        $delete = $this->getGlobalOption('delete','showtable',$this->table);
        $tpl->assign('edit',$edit);
        $tpl->assign('delete',$delete);

        $do = M_Office_Util::doForTable($this->table);
        $do->{$this->field}=$this->value;
        if(is_array($do->fb_linkOrderFields)) {
          $do->orderBy();
          $do->orderBy(implode(',',$do->fb_linkOrderFields));
        }
        $do->find();
        $tpl->assignRef('do',$do);
        $keys = $do->keys();
        $pk = $keys[0];
        $tpl->assign('pk',$pk);
        return $tpl->fetch('ajaxfromtable/bloc');
    }
    function getLine($recordId) {
        $do = M_Office_Util::doForTable($this->table);
        $do->get($recordId);
        return $this->renderLine($do);
    }
    function renderLine($do,$layouted=false) {
        $tpl = $this->tplInstance($this->module);
        $keys = $do->keys();
        $pk = $keys[0];
        $tpl->assign('pk',$pk);
        $tpl->assign('do',$do);
        $tpl->assign('table',$this->table);
        $tpl->assign('field',$this->field);
        $tpl->assign('value',$this->value);        
        $edit = $this->getGlobalOption('edit','showtable',$this->table);
        $delete = $this->getGlobalOption('delete','showtable',$this->table);
        $tpl->assign('edit',$edit);
        $tpl->assign('delete',$delete);
        $tpl->assign('layouted',$layouted);
        return $tpl->fetch('ajaxfromtable/ajaxline');
    }
    function deleteRecord($recordId) {
        $do = M_Office_Util::doForTable($this->table);
        if($do->get($recordId) && $do->delete()) {
                header("HTTP/1.0 200 OK");
                echo 'ok';
                exit;
        } else {
          header("HTTP/1.0 406 Not Allowed");
          echo 'Impossible de supprimer enregistrement '.$recordId.' de '.$this->table;
          exit;
          
        };
    }
    function showError($message) {
        $error='';
        if(is_array($message)) {
            foreach($message as $key=>$err) {
                $error.='- Champ '.$key.' : '.$err.'<br />';
            }
        } else {
            $error = $message;
        }
        return '<li class="ajaxFromTableError">'.$error.'</li>';
    }
    function addRecord() {
        $form = $this->getAddFormObject();
        if($form->validate()) {
            $this->fb->_do->{$this->field}=$this->value;
            $form->process(array($this->fb,'processForm'),false);
        } else {
            return $this->showError($form->_errors);
        }
        return $this->renderLine($this->fb->_do,true);
    }
    function updateRecord($recordId) {
        $form = $this->getAddFormObject($this->getObject($recordId));
        if($form->validate()) {
            $form->process(array($this->fb,'processForm'),false);
            $this->fb->_do->{$this->field}=$this->value;
            $this->fb->_do->update();
        } else {
            return $this->showError($form->_errors).$this->outForm($form);
        }
        return $this->renderLine($this->fb->_do,false);
    }
    
    function getAddForm($recordId = null) {
      $record = $this->getObject($recordId);
      return $this->outForm($this->getAddFormObject($record),$record);
    }
    function outForm($form,$object) {
      $tpl = $this->tplInstance($this->module);
      $tpl->assignRef('form',$form);
      $tpl->assignRef('do',$object);
      return $tpl->fetch('ajaxfromtable/addform');
    }
    function &getObject($recordId = null) {
      $do = M_Office_Util::doForTable($this->table);
      $do->{$this->field}=$this->value;
      if(!is_null($recordId)) {
          $do->get($recordId);
      }
      return $do;
    }
    function &getAddFormObject($record = null) {
        if(is_null($record)) {
          $record = $this->getObject();
        }
        if(!$record->isNew()) {            
            $formaction = 'updateFromTableRecord';
            $formclass='updateFromTableForm';
            $formactionval = $record->pk();
            $formname = 'addFromTable__'.$this->table.$record->pk();
            $update= $this->table.$record->pk();
            $sub="OK";
        } else {
            $formaction = 'addFromTableRecord';
            $formclass='addFromTableForm';
            $formactionval = 1;
            $formname = 'addFromTable__'.$this->table;
            $update = 'endList__'.$this->table;
            $sub="+";
        }
        $form = & new HTML_QuickForm($formname,'POST',M_Office_Util::getQueryParams(array('module'=>$this->table,'filterField'=>$this->field,'filterValue'=>$this->value,$formaction=>$formactionval,'ajaxfromtable'=>1),array_keys($_REQUEST),false));
        $form->updateAttributes(array('class'=>$formclass,'target'=>$update));
        Mtpl::addJS('jquery.forms');
        $record->fb_requiredRuleMessage = __('The field "%s" is required');
        $record->fb_ruleViolationMessage = __('The field "%s" is not valid');
        $record->fb_formAddHeader = true;
        $record->fb_createSubmit=false;
        $record->fb_submitText=$sub;
        $this->fb = & MyFB::create($record);
        $this->fb->useForm($form);
        $this->fb->getForm();
        if($form->elementExists($this->field)) {
            $form->removeElement($this->field);
        }
        $form->addElement('submit','__submit__','+');
        $form->addElement('hidden',$this->field,$this->value);        
//        $form->addElement('static','st','','<pre>'.print_r($_REQUEST,true).'</pre>');
        return $form;
    }
    function processRequest() {
      // TODO More control over the template - ALMOST DONE
      $this->assign('__action','ajaxfromtable');
        switch(true) {
            case key_exists('editFromTableRecord',$_REQUEST);
                $this->assign('output',$this->getAddForm($_REQUEST['editFromTableRecord']));
            break;
            case key_exists('addFromTableRecord',$_REQUEST):

                $this->assign('output',$this->addRecord());
            break;
            case key_exists('updateFromTableRecord',$_REQUEST):
                $this->assign('output',$this->updateRecord($_REQUEST['updateFromTableRecord']));
            break;
            case key_exists('deleteFromTableRecord',$_REQUEST):
                $this->assign('output',$this->deleteRecord($_REQUEST['deleteFromTableRecord']));
            break;
        }        
    }
}