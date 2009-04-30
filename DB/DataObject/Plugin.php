<?php
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
	/**
	 * Returns folder name into which can be stored resources used by the plugin (e.g. actions templates)
	 * The folder must have the same name as the plugin's class file (Like PEAR packages).
	 * This method DOES NOT return a path, it just returns the folder name !
	 * @return string folder name
	 */
  public function getFolderName()
  {
    $info = DB_DataObject_Pluggable::getPluginInfo($this);
    return basename($info['include_file'],'.php');
  }
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