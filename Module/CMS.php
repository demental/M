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
* @subpackage   Module_CMS
*/
/**
* M PHP Framework
*
* Module extension that automagically fetches and assigns to the view as database CMS record using the action value
*
* @package      M
* @subpackage   Module_CMS
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Module_CMS extends Module {

  protected $_dbtable='cms';
  protected $_dbstrip='strip';
  protected $_tpltitle='pageTitle';
  protected $_dbtitle='titrelong';

  protected function populateCMS()
  {
    $content = DB_DataObject::factory($this->_dbtable);
    if(!$content->get($this->_dbstrip,$this->_dataAction)) {
      $this->_dbnotfound=1;
      return;
    }
    $this->assignRef('content',$content);
    try{
      Mreg::set('content',$content);
    } catch (Exception $e) {
      
    }
    $this->assign($this->_tpltitle,$content->{$this->_dbtitle});
  }
  
  public function preExecuteAction($action)
  {
    $this->populateCMS();
  }
  public function executeAction($action)
  {
    $this->_dataAction = $action;
    try {
      parent::executeAction($action);
    } catch (Error404Exception $e) {
      if($this->_dbnotfound) {
        throw new Error404Exception('content data not found !');
      }
      $action='index';
      parent::executeAction($action);
    }
  }
  public function output($template=null,$layout=null)
  {
    try {
      $out = parent::output($template,$layout);
    } catch(Exception $e) {
      $out = parent::output(strtolower(str_replace('Module_', '', get_class($this))).'/index',$layout);
    }
    return $out;
  }
}?>