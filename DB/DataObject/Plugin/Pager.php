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
* @subpackage   DB_DataObject_Plugin_Pager
*/
/**
* M PHP Framework
*
* Pager plugin
* This is a lightweight alternative to Structures_DataGrid when the only need is to provide paged HTML results
* Attachs a PEAR_Pager to the DBDO object and automatically adds LIMIT directive to the query
*
* @package      M
* @subpackage   DB_DataObject_Plugin_Pager
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'M/DB/DataObject/Plugin.php';
class DB_DataObject_Plugin_Pager extends DB_DataObject_Plugin
{
    public $plugin_name='pager';	
    public $pager;
    public $hasPager=true;
    public $pointer = array(
      'sort'=>null,
      'direction'=>'ASC'
      );
    public $vars = array(
      'sort'=>'_ps',
      'direction'=>'_pd'
      );
    function setVars($sort,$direction) {
      $this->vars['sort']=$sort;
      $this->vars['direction']=$direction;           
    }
    
    function setValues($sort,$direction) {
      $this->pointer['sort']=$sort;
      $this->pointer['direction']=$direction;      
    }
    
    function find($autoFetch=false,&$obj) {
      if($autoFetch) {
        return;
      }
      $this->preparePager($obj);
    }
    function preparePager($obj) {
      $c = clone($obj);
      $this->pagerOpts['totalItems'] = $this->totalItems = $c->count();

      if($this->hasPager) {
        require_once 'Pager.php';

        $this->pager = Pager::factory($this->pagerOpts);  
        $lim=$this->pager->getOffsetByPageId();
        $obj->limit(($lim[0]-1),($lim[1]+1-$lim[0]));
      }
      if($this->pointer['sort']) {
        $obj->orderBy($obj->tableName().'.'.$this->pointer['sort'].' '.$this->pointer['direction']);
      } elseif($this->defaultSort) {
        $obj->orderBy($obj->tableName().'.'.implode(',',$this->defaultSort));
      }
    }
    function setOptions($opt) {
      $this->pagerOpts = array_merge($opt,$this->pagerOpts);
    }
    function setOption($var,$val) {
      $this->pagerOpts[$var]=$val;

    }
    function setDefaultSort($sort) {
      $this->defaultSort=$sort;
    }
    function query($q=false,&$obj) {
        require_once 'Pager.php';
        $this->preparePager($obj);
    }
    function &getPager() {

      return $this->pager;
    }
    function getSortLink($field) {

      $get=$_GET;
      $get[$this->vars['sort']]=$field;
      $get[$this->vars['direction']]=($_GET[$this->vars['sort']]==$field?($_GET[$this->vars['direction']]=='ASC'?'DESC':'ASC'):'ASC');
      return $_SERVER['PHP_SELF'].'?'.http_build_query($get);
    }
    function setFields($fields) {
      $this->fields = $fields;
    }
    function getFields() {
      if(!$this->fields) {
        $this->fields = array_keys($this->_obj->table());
      }
      return $this->fields;
    }
}