<?php
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