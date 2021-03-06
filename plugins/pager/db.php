<?php
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
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Plugins_Pager_DB extends M_Plugin
{
    public $plugin_name='pager';
    public $pager;
    public $hasPager=true;
    public $pagerOpts = array();
    public $pointer = array(
      'sort'=>null,
      'direction'=>'ASC'
      );
    public $vars = array(
      'sort'=>'_ps',
      'direction'=>'_pd'
      );
    public function getEvents() {
      return array('find','query','getpager');
    }
    public function setVars($sort,$direction) {
      $this->vars['sort']=$sort;
      $this->vars['direction']=$direction;
    }

    public function setValues($sort,$direction) {
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
      $this->pagerOpts['totalItems'] = $this->totalItems = $c->count('distinct');

      if($this->hasPager) {
        $obj->_pager = Pager::factory($this->pagerOpts);
        $lim=$obj->_pager->getOffsetByPageId();
        $obj->limit(($lim[0]-1),($lim[1]+1-$lim[0]));
      }
      if($this->pointer['sort']) {
        if(strpos($this->pointer['sort'],'.')!==false) {
          $obj->orderBy($this->pointer['sort'].' '.$this->pointer['direction']);
        } else {
          $obj->orderBy($obj->tableName().'.'.$this->pointer['sort'].' '.$this->pointer['direction']);
        }
      } elseif($this->defaultSort) {
        $ord = $obj->tableName().'.'.implode(',',$this->defaultSort);
        $obj->orderBy($ord);
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
        $this->preparePager($obj);
    }
    function getPager($obj) {
      return self::returnStatus($obj->_pager);
    }
    function getSortLink($field) {

      $get=$_GET;
      $get[$this->vars['sort']]=$field;
      $get[$this->vars['direction']]=($_GET[$this->vars['sort']]==$field?($_GET[$this->vars['direction']]=='ASC'?'DESC':'ASC'):'ASC');
      return $_SERVER['PHP_SELF'].'?'.http_build_query($get,'','&amp;');
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
