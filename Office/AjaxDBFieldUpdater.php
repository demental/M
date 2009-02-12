<?php
// ===============
// = Not used... =
// ===============
class M_Office_AjaxDBFieldUpdater extends M_Office_Controller
{
	var $do; // DB_DataObjects
	var $table; // String
	var $field; // String
	var $value; // String
	
	function M_Office_AjaxDBFieldUpdater(){
		switch($_REQUEST['ajaction']){
			case 'updateField':
				$this->updateField($_REQUEST);
				break;
				case 'invertBool':
					$this->invertBool($_REQUEST);
				break;
				default:return false;
				break;
				}
	function &getDO($data){
		$do=& DB_DataObject::factory($data['table']);
		$do->get($data['record']);
		return $do;
	}
	function updateField($data){
		$do=& $this->getDO($data);
		$do->$data['field']=$data['value'];
		return $do->update();
	}
	function invertBool($data){
		$do=& $this->getDO($data);
		$do->$data['field']=!$do->data['field'];
		return $do->update();
	}
}