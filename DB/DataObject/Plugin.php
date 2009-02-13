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
* @subpackage   DB_DataObject_Plugin
*/
/**
* M PHP Framework
*
* Abstract class for DB_DataObject_Pluggable plugins
*
* @package      M
* @subpackage   DB_DataObject_Plugin
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


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