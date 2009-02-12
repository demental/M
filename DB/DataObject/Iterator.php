<?php
// =====================================
// = Iterator interface implementation
// = Makes DB_DataObject traversable
// = WARNING ! Only works with MDB2 Driver !!
// =====================================
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