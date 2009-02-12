<?php

/**
 * Abstract class for DB_DataObject_Pluggable plugins
 **/

abstract class DB_DataObject_Plugin
{
	var $_dataObject;
	protected $_autoActions = true;
    
	function setAutoActions($bool) {
	    $this->_autoActions = $bool;
	}
	function register(&$obj)
	{
		$this->_dataObject = &$obj;
	}

	function preGenerateForm(&$fb,&$obj)
	{
		return;
	}
	function postGenerateForm(&$form,&$fb,&$obj)
	{
		return;
	}
	function preProcessForm(&$values,&$fb,&$obj)
	{
		return;
	}
	function prepareLinkedDataObject(&$linkedDataObject, $field,&$obj)
	{
		return;
	}
	function getGlobalMethods(&$obj) {
	    return;
	}
	function getBatchMethods(&$obj) {
	    return;
	}
	function getSingleMethods(&$obj) {
	    return;
	}
	function postProcessForm(&$v,&$fb,&$obj)
	{
		return;
	}
	function insert(&$obj)
	{
		return;
	}
	function postinsert(&$obj)
	{
		return;
	}
	function update(&$obj)
	{
		return;
	}
	function postupdate(&$obj)
	{
		return;
	}
	function prefetch(&$obj)
	{
		return;
	}
	function postfetch(&$obj)
	{
		return;
	}
	function find($autoFetch=false,&$obj)
	{
		return;
	}
	function count(&$obj)
	{
	  return;
	}
	function delete(&$obj)
	{
		return;
	}
	function postdelete(&$obj)
	{
		return;
	}
  function dateOptions($field, &$fb,&$obj) {
		return;
	}
}