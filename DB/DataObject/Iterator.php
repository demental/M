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
* @subpackage   DB_DataObject_Iterator
*/
/**
* M PHP Framework
*
* Iterator interface implementation
* Makes DB_DataObject traversable
* WARNING ! Only works with MDB2 Driver !!
*
* @package      M
* @subpackage   DB_DataObject_Iterator
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class DB_DataObject_Iterator extends DB_DataObject implements Iterator {
    public function current() {
        return $this;
    }
    public function key() {
        global $_DB_DATAOBJECT;
        $result = &$_DB_DATAOBJECT['RESULTS'][$this->_DB_resultid];
        return $result->rowCount();
    }
    public function next() {
        $this->fetch();
    }
    public function rewind() {
        global $_DB_DATAOBJECT;
        $result = &$_DB_DATAOBJECT['RESULTS'][$this->_DB_resultid];
        if(!$result) return false;
        $result->seek();
        $this->fetch();
    }
    public function valid() {
        if(empty($this->N)) {
            return false;
        }
        global $_DB_DATAOBJECT;
        if (empty($_DB_DATAOBJECT['RESULTS'][$this->_DB_resultid]) || 
            !is_object($result = &$_DB_DATAOBJECT['RESULTS'][$this->_DB_resultid])) 
        {
            return false;
        }
        return true;
    }
}