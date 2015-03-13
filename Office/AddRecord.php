<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Record insertion handling
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


class M_Office_AddRecord extends M_Office_Controller {
    public function __construct($module) {
        parent::__construct();
        $this->assign('__action','add');

        $tpl = Mreg::get('tpl');
        $tpl->concat('adminTitle',' :: '.$this->moduloptions['title'].' :: '.__('Add record'));

    		$mopts = PEAR::getStaticProperty('m_office','options');
        $mopt = $mopts['modules'][$module];
    		$table = $mopt['table'];
        $do = M_Office_Util::doForModule($module);

        $this->append('subActions','<a href="'.M_Office_Util::getQueryParams(array(), array('record','doSingleAction')).'">'.__('Go to the %s list',array($mopt['title'])).'</a>');
        $formBuilder =& MyFB::create($do);

//	linkNewValue creates an issue if some linked elements are put in predefgroups
//        $formBuilder->linkNewValue = true;
        $form = new MyQuickForm('editRecord', 'POST', M_Office_Util::getQueryParams(array(), array(), false), '_self', null, true);
        $form->addElement('hidden', 'submittedNewRecord', 1);
        if (isset($_REQUEST['filterField'])) {
            $form->addElement('hidden', 'filterField', $_REQUEST['filterField']);
            $form->addElement('hidden', 'filterValue', $_REQUEST['filterValue']);
            $do->{$_REQUEST['filterField']} = $_REQUEST['filterValue'];
        }
        $links = $do->links();
        if(key_exists($_REQUEST['filterField'], $links)) {
            $linfo = explode(':',$links[$_REQUEST['filterField']]);
            $form->addElement('static','mod','',__('Add record with %s = %s',array($_REQUEST['filterField'],$_REQUEST['filterValue'])).'. '.'<a href="'.M_Office_Util::getQueryParams(array('table'=>$linfo[0],'record'=>$_REQUEST['filterValue']),array('addRecord','filterField','filterValue')).'">'.__('Back to main record').'</a>');
        }


        $formBuilder->useForm($form);
        $formBuilder->getForm();

        if ($this->getOption('createAnother', $table)) {
            $form->addElement('static', '&nbsp;', '&nbsp;');
            $form->addElement('checkbox', 'returnHere', __('Create another record'));
            if (isset($_REQUEST['returnHere']) && $_REQUEST['returnHere']) {
                $form->setDefaults(array('returnHere' => true));
            }
        }
        M_Office_Util::addHiddenFields($form);
        if ($form->validate()) {
            if (PEAR::isError($ret = $form->process(array(&$formBuilder, 'processForm'), false))) {
                $this->append('error',__('An error occured while inserting record').' : '.$ret->getMessage());
            }else {
              $pk = DB_DataObject_FormBuilder::_getPrimaryKey($do);
              $this->say(__('New record was successfully created. Its identifier is : %s',array($do->$pk)));
              if ($this->getOption('createAnother', $table) && isset($_REQUEST['returnHere']) && $_REQUEST['returnHere']) {
                M_Office_Util::refresh(M_Office_Util::getQueryParams(array('returnHere' => $_REQUEST['returnHere']), array(), false));
              } else {
                $pk = DB_DataObject_FormBuilder::_getPrimaryKey($do);
                M_Office_Util::refresh(M_Office_Util::getQueryParams(array('record'=>$do->$pk), array('returnHere', 'addRecord'), false));
              }
            }
        } elseif($form->isSubmitted()) {
        }
        $this->assign('addForm',$form);
        $this->assign('do',$do);
    }
    public function run()
    {
      # code...
    }
}