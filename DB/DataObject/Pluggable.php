<?php
/**
* M PHP Framework
* @package      M
* @subpackage   DB_DataObject_Pluggable
*/
/**
* M PHP Framework
*
* Plugin extension for DB_DataObject
* Extends DB_DataObject_Iterator
*
* @package      M
* @subpackage   DB_DataObject_Pluggable
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


require_once 'DB/DataObject.php';

if(!defined('PLUGIN_DIR')) {
	define('PLUGIN_DIR','M/DB/DataObject/Plugin/');
}

if(!function_exists('__')){
	function __($data){
		return $data;
	}
}


function date2array($dt){
    if ( $dt==NULL )
    {
        return array('Y'=>'0000','m'=>'00','d'=>'00');
    }
    else
    {
        require_once 'DB/DataObject/FormBuilder.php';
        return DB_DataObject_FormBuilder::_date2array($dt);
    }
}

define('REQUIRED_RULE_MESSAGE',__("Le champ ci-dessous est requis."));
define('VIOLATION_RULE_MESSAGE',__("Le champ ci-dessous n'est pas valide."));
define('NEWVALUE_TEXT',__("--Nouveau--"));

class DB_DataObject_Pluggable extends DB_DataObject implements Iterator {

  protected $_listeners = array();


	public $fb_requiredRuleMessage=REQUIRED_RULE_MESSAGE;
	public $fb_ruleViolationMessage=VIOLATION_RULE_MESSAGE;
	public $fb_linkNewValueText=NEWVALUE_TEXT;
  public $fb_dateFromDatabaseCallback='date2array';

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


################ Plugin management ################

  /**
   * Returns an array containing plugin definitions. This method MUST be overriden
   * if you want to use DB plugins.
   * @example :
   * <?php
   * class DataObjects_Blogpost extends DB_DataObject_Pluggable
   * {
   * .....
   * 
   * public function _getPluginsDef() {
   *  return array(
   *      'i18n'=>array('name','title'),
   *      'officePack'=>true,
   *      );
   * }
   */
  public function _getPluginsDef() {
    return array();
  }

  /**
   * Binds all needed plugins
   * The plugin declaration resides in the method $myDataObject->_getPluginsDef()
   * This method is executed only once per DBDO instance.
   **/

	public function _loadPlugins() {
    if($this->_pluginsLoaded) return;
    foreach($this->_getPluginsDef() as $pluginName=>$params) {
      $this->addListener(PluginRegistry::getInstance($pluginName,'DB'));
    }
    $this->_pluginsLoaded = true;
	}
  /**
   * Loads a plugin, provided its identifier name
   * @param string $pname name of the plugin
   **/
	public function loadPlugin($pluginName) {
    $defs = $this->_getPluginsDef();
    $params = $defs[$pluginName];
    $this->addListener(PluginRegistry::getInstance($pluginName,'DB'));    
    return $this;
  }
  /**
  * unloads a plugin, provided its identifier name
  * @access public
  * @param string $pname name of the plugin to unload
  **/

	public function unloadPlugin($pluginName) {
    $this->removeListener(PluginRegistry::getInstance($pluginName,'DB'));
    return $this;
  }
  /**
  * Unbinds all binded plugins
  * @access public
  **/
  public function unloadPlugins()
  {
    if(!$this->_pluginsLoaded) return;
    foreach($this->_getPluginsDef() as $pluginName=>$params) {
      $this->addListener(PluginRegistry::getInstance($pluginName,'DB'));
    }
    $this->_pluginsLoaded = false;
    return $this;
  }
	/**
	 * Returns a reference for a plugin, provided its identifier name
	 * @access public
	 * @param string $pname name of the requested plugin
	 * @return false if the plugin does not exist in the current object
	 * @return Object plugin reference
	 **/
	public function getPlugin($pname) {
  	if(!$this->_pluginsLoaded) {
  	    $this->loadPlugin($pname);
  	}
    return PluginRegistry::getInstance($pname,'DB');
	}
  public function addListener($listener)
  {
    if(!$this->hasListener($listener)) {
      $this->_listeners[] = $listener;
    }
    return $this;
  }
  public function removeListener($listener)
  {
    foreach($this->_listeners as $i=>$alistener) {
      if($alistener === $listener) {
        unset($this->_listeners[$i]);
      }
    }
    return $this;
  }
  public function removeAllListeners()
  {
    $this->_listeners = array();
    return $this;
  }
  public function hasListener($listener)
  {
    foreach($this->_listeners as $alistener) {
      if($listener === $alistener) return true;
    }
    return false;
  }
  /**
   * @param string name of the event
   * @param mixed parameters passed to the events
   */
  public function trigger($eventName,$params = null)
  {
    $eventName = strtolower($eventName);
    if(!$this->_pluginsloaded) {
      $this->_loadplugins();
    }
    $finalresult = null;
    foreach($this->_listeners as $listener) {
      $result = $listener->handleEvent($this,$eventName,$params);
      if($result == 'fail') return 'fail';
      if($result == 'bypass') $finalresult = 'bypass';
    }
    return $finalresult;
    
  }
  /**
   * Overload => transform it to an event call
   */
  public function __call($method,$args)
  {
    $this->trigger($method,$args);
    return parent::__call($method,$args);
  }
###################### End plugin management #######################

  /**
   * Adds ability to get records by an array of primary keys
   */
  public function get($k = null,$v = null) {
    if(is_array($k)) {
        $k = implode(',',$k);
        $this->whereAdd('id IN('.$k.')');
        return $this->find();
    } else {
        return parent::get($k,$v);
    }
  }
  /**
   * Delete linked records
   * @param $param1,$param2,...,$paramN strings foreign table names
   */
  function deleteLinks() {
    $args=func_get_args();
    foreach($args as $tbl){
      $this->say('Deleting foreign records in '.$tbl);
      $do=& DB_dataObject::factory($tbl);
      $links=$do->links();
      foreach ($links as $field=>$link) {
        if(preg_match('`^'.$this->tableName().':.+$`',$link,$match)){
          $do->$field=$this->pk();
          break;
        }
      }
      while($do->fetch()){
        $do->delete();
        $this->say('Deleting '.$do->tableName().' '.$do->id);
      }
    }
  }

// =======================================================
// = DB_DataObject methods override - events triggered   =
// =======================================================    
	function preGenerateForm(&$fb){
		$this->trigger('preGenerateForm',array(&$fb));
		if(empty($this->fb_submitText)){
			$this->fb_submitText=__('Submit');
		}
	}	
	function postGenerateForm(&$form,&$fb){
		$this->trigger('postGenerateForm',array(&$form,&$fb));
		$form->setJsWarnings(__("The following fields are not valid"),__("Please correct them"));
		$form->setRequiredNote(__("Required fields"));
	}
	
	function prepareLinkedDataObject(&$linkedDataObject, $field){
		$this->trigger('prepareLinkedDataObject',array($linkedDataObject,$field));
	}
	function preProcessForm(&$v,&$fb){
		$this->trigger('preProcessForm',array(&$v,&$fb));
	}
	function postProcessForm(&$v,&$fb){
		$this->trigger('postProcessForm',array(&$v,&$fb));
	}				
	function insert(){
	  $this->getDatabaseConnection()->query('set names utf8');
		$result = $this->trigger('insert');
		switch($result) {
		  case 'bypass':
		    return true;
		    break;
		  case 'fail':
		    return false;
		    break;
		  default:  
        if(parent::insert()) {
    		  $this->trigger('postinsert');
          return true;
        }
        return false;
    }
  }
	function update($do = false){
    $this->getDatabaseConnection()->query('set names utf8');
		$result = $this->trigger('update',array($do));
		switch($result) {
		  case 'bypass':
		    return true;
		    break;
		  case 'fail':
		    return false;
		    break;
		  default:  
      if(parent::update($do)!==false) {
    		$this->trigger('postupdate');
        return true;
      }
      return false;		
    }
	}

	function fetch(){
		$this->trigger('prefetch');
		if(parent::fetch()){
  		$this->trigger('postfetch');
			return true;
		}
		return false;
	}

	function find($autoFetch=false){
    $this->getDatabaseConnection()->query('set names utf8');
		$this->trigger('find',array($autoFetch));
		return parent::find($autoFetch);
	}
	function query($req){
    $this->getDatabaseConnection()->query('set names utf8');
		$this->trigger('query',array($req));
    return parent::query($req);
  }
	public function count(){
		$this->trigger('count');
		return parent::count();
	}
	
	public function delete()
	{
		$res = $this->trigger('delete');
		switch($result) {
		  case 'bypass':
		    return true;
		    break;
		  case 'fail':
		    return false;
		    break;
		  default:  
      if(parent::delete()!==false) {
    		$this->trigger('postdelete');
        return true;
      }
      return false;		
    }
  }
		
  public function dateOptions($field, &$fb) {
		$this->trigger('dateOptions',array($field,$fb));
    return array('format' => 'd-m-Y','addEmptyOption'=>true,'emptyOptionText'=>array('Y'=>'YYYY','m'=>'mm','d'=>'dd'));
  }

  public function getSingleMethods($base = null) {
    if(!is_array($base)) {
        $base = array();
    }
    $this->trigger('getSingleMethods',array($base));
    return $base;
  }
  public function getGlobalMethods($base = null) {
    if(!is_array($base)) {
      $base = array();
    }
    $this->trigger('getGlobalMethods',array($base));
    return $base;
  }
  public function getBatchMethods($base = null) {
    if(!is_array($base)) {
      $base = array();
    }
    $this->trigger('getBatchMethods',array($base));
    return $base;
  }
	// =========================================
	// = joinAdd patch over original DB_DataObject class =
	// =========================================
    function joinAdd($obj = false, $joinType='INNER', $joinAs=false, $joinCol=false)
    {
        global $_DB_DATAOBJECT;
        if ($obj === false) {
            $this->_join = '';
            return;
        }
        
        // support for array as first argument 
        // this assumes that you dont have a links.ini for the specified table.
        // and it doesnt exist as am extended dataobject!! - experimental.
        
        $ofield = false; // object field
        $tfield = false; // this field
        $toTable = false;
        if (is_array($obj)) {
            $tfield = $obj[0];
            list($toTable,$ofield) = explode(':',$obj[1]);
            $obj = DB_DataObject::factory($toTable);
            
            if (!$obj || is_a($obj,'PEAR_Error')) {
                $obj = new DB_DataObject;
                $obj->__table = $toTable;
            }
            $obj->_connect();
            // set the table items to nothing.. - eg. do not try and match
            // things in the child table...???
            $items = array();
        }
        
        if (!is_object($obj) || !is_a($obj,'DB_DataObject')) {
            return $this->raiseError("joinAdd: called without an object", DB_DATAOBJECT_ERROR_NODATA,PEAR_ERROR_DIE);
        }
        /*  make sure $this->_database is set.  */
        $this->_connect();
        $DB = &$_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5];
       

        
        
         /* look up the links for obj table */
        //print_r($obj->links());
        if (!$ofield && ($olinks = $obj->links())) {
            
            foreach ($olinks as $k => $v) {
                /* link contains {this column} = {linked table}:{linked column} */
                $ar = explode(':', $v);
                
                // Feature Request #4266 - Allow joins with multiple keys
                
                $links_key_array = strpos($k,',');
                if ($links_key_array !== false) {
                    $k = explode(',', $k);
                }
                
                $ar_array = strpos($ar[1],',');
                if ($ar_array !== false) {
                    $ar[1] = explode(',', $ar[1]);
                }
             
                if ($ar[0] == $this->__table) {
                    
                    // you have explictly specified the column
                    // and the col is listed here..
                    // not sure if 1:1 table could cause probs here..
                  /*  
                    if ($joinCol !== false) {
                        $this->raiseError( 
                            "joinAdd: You cannot target a join column in the " .
                            "'link from' table ({$obj->__table}). " . 
                            "Either remove the fourth argument to joinAdd() ".
                            "({$joinCol}), or alter your links.ini file.",
                            DB_DATAOBJECT_ERROR_NODATA);
                        return false;
                    }
                */
                if ($joinCol === false) {
                    
                    $ofield = $k;
                    $tfield = $ar[1];
                    break;
                }
                }
            }
        }

        /* otherwise see if there are any links from this table to the obj. */
        //print_r($this->links());
        if (($ofield === false) && ($links = $this->links())) {
            foreach ($links as $k => $v) {
                /* link contains {this column} = {linked table}:{linked column} */
                $ar = explode(':', $v);
                if ($ar[0] == $obj->__table) {
                    if ($joinCol !== false) {
                        if ($k == $joinCol) {
                            $tfield = $k;
                            $ofield = $ar[1];
                            break;
                        } else {
                            continue;
                        }
                    } else {
                        $tfield = $k;
                        $ofield = $ar[1];
                        break;
                    }
                }
            }
        }
        // finally if these two table have column names that match do a join by default on them

        if (($ofield === false) && $joinCol) {
            $ofield = $joinCol;
            $tfield = $joinCol;

        }
        /* did I find a conneciton between them? */

        if ($ofield === false) {
            $this->raiseError(
                "joinAdd: {$obj->__table} has no link with {$this->__table}",
                DB_DATAOBJECT_ERROR_NODATA);
            return false;
        }
        $joinType = strtoupper($joinType);
        
        // we default to joining as the same name (this is remvoed later..)
        
        if ($joinAs === false) {
            $joinAs = $obj->__table;
        }
        
        $quoteIdentifiers = !empty($_DB_DATAOBJECT['CONFIG']['quote_identifiers']);
        
        // not sure  how portable adding database prefixes is..
        $objTable = $quoteIdentifiers ? 
                $DB->quoteIdentifier($obj->__table) : 
                 $obj->__table ;
                
        $dbPrefix  = '';
        if (strlen($obj->_database) && in_array($DB->dsn['phptype'],array('mysql','mysqli'))) {
            $dbPrefix = ($quoteIdentifiers
                         ? $DB->quoteIdentifier($obj->_database)
                         : $obj->_database) . '.';    
        }
        
        // if they are the same, then dont add a prefix...                
        if ($obj->_database == $this->_database) {
           $dbPrefix = '';
        }
        // as far as we know only mysql supports database prefixes..
        // prefixing the database name is now the default behaviour,
        // as it enables joining mutiple columns from multiple databases...
         
            // prefix database (quoted if neccessary..)
        $objTable = $dbPrefix . $objTable;
       
         
        
        
        
        // nested (join of joined objects..)
        $appendJoin = '';
        if ($obj->_join) {
            // postgres allows nested queries, with ()'s
            // not sure what the results are with other databases..
            // may be unpredictable..
            if (in_array($DB->dsn["phptype"],array('pgsql'))) {
                $objTable = "($objTable {$obj->_join})";
            } else {
                $appendJoin = $obj->_join;
            }
        }
        
        
        $table = $this->__table;
        
        if ($quoteIdentifiers) {
            $joinAs   = $DB->quoteIdentifier($joinAs);
            $table    = $DB->quoteIdentifier($table);     
            $ofield   = $DB->quoteIdentifier($ofield);    
            $tfield   = $DB->quoteIdentifier($tfield);    
        }
        // add database prefix if they are different databases
       
        
        $fullJoinAs = '';
        $addJoinAs  = ($quoteIdentifiers ? $DB->quoteIdentifier($obj->__table) : $obj->__table) != $joinAs;
        if ($addJoinAs) {
            // join table a AS b - is only supported by a few databases and is probably not needed
            // , however since it makes the whole Statement alot clearer we are leaving it in
            // for those databases.
            $fullJoinAs = in_array($DB->dsn["phptype"],array('mysql','mysqli','pgsql')) ? "AS {$joinAs}" :  $joinAs;
        } else {
            // if 
            $joinAs = $dbPrefix . $joinAs;
        }
        
        
        switch ($joinType) {
            case 'INNER':
            case 'LEFT': 
            case 'RIGHT': // others??? .. cross, left outer, right outer, natural..?
                
                // Feature Request #4266 - Allow joins with multiple keys
                $this->_join .= "\n {$joinType} JOIN {$objTable} {$fullJoinAs}";
                if (is_array($ofield)) {
                	$key_count = count($ofield);
                    for($i = 0; $i < $key_count; $i++) {
                    	if ($i == 0) {
                    		$this->_join .= " ON {$joinAs}.{$ofield[$i]}={$table}.{$tfield[$i]} {$appendJoin} ";
                    	}
                    	else {
                    		$this->_join .= " AND {$joinAs}.{$ofield[$i]}={$table}.{$tfield[$i]} {$appendJoin} ";
                    	}
                     }
                } else {
	                $this->_join .= " ON {$joinAs}.{$ofield}={$table}.{$tfield} {$appendJoin} ";
                }

                break;
                
            case '': // this is just a standard multitable select..
                $this->_join .= "\n , {$objTable} {$fullJoinAs} {$appendJoin}";
                $this->whereAdd("{$joinAs}.{$ofield}={$table}.{$tfield}");
        }
         
        // if obj only a dataobject - eg. no extended class has been defined..
        // it obvioulsy cant work out what child elements might exist...
        // untill we get on the fly querying of tables..
        if ( strtolower(get_class($obj)) == 'db_dataobject') {
            return true;
        }
         
        /* now add where conditions for anything that is set in the object */
    
    
    
        $items = $obj->table();
        // will return an array if no items..
        
        // only fail if we where expecting it to work (eg. not joined on a array)
        
        
        
        if (!$items) {
            $this->raiseError(
                "joinAdd: No table definition for {$obj->__table}", 
                DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }

        foreach($items as $k => $v) {
            if (!isset($obj->$k)) {
                continue;
            }
            
            $kSql = ($quoteIdentifiers ? $DB->quoteIdentifier($k) : $k);
            
            
            if ($v & DB_DATAOBJECT_STR) {
                $this->whereAdd("{$joinAs}.{$kSql} = " . $this->_quote((string) (
                        ($v & DB_DATAOBJECT_BOOL) ? 
                            // this is thanks to the braindead idea of postgres to 
                            // use t/f for boolean.
                            (($obj->$k === 'f') ? 0 : (int)(bool) $obj->$k) :  
                            $obj->$k
                    )));
                continue;
            }
            if (is_numeric($obj->$k)) {
                $this->whereAdd("{$joinAs}.{$kSql} = {$obj->$k}");
                continue;
            }
                        
            if (is_a($obj->$k,'DB_DataObject_Cast')) {
                $value = $obj->$k->toString($v,$DB);
                if (PEAR::isError($value)) {
                    $this->raiseError($value->getMessage() ,DB_DATAOBJECT_ERROR_INVALIDARG);
                    return false;
                }
                if (strtolower($value) === 'null') {
                    $this->whereAdd("{$joinAs}.{$kSql} IS NULL");
                    continue;
                } else {
                    $this->whereAdd("{$joinAs}.{$kSql} = $value");
                    continue;
                }
            }
            
            
            /* this is probably an error condition! */
            $this->whereAdd("{$joinAs}.{$kSql} = 0");
        }
        if (!isset($this->_query)) {
            $this->raiseError(
                "joinAdd can not be run from a object that has had a query run on it,
                clone the object or create a new one and use setFrom()", 
                DB_DATAOBJECT_ERROR_INVALIDARGS);
            return false;
        }
        // and finally merge the whereAdd from the child..
        if (!$obj->_query['condition']) {
            return true;
        }
        $cond = preg_replace('/^\sWHERE/i','',$obj->_query['condition']);
        
        $this->whereAdd("($cond)");
        return true;
    }
    
    // ==================
    // = Helper methods =
    // ==================
 
  	/**
  	 * Notifications
  	 * Sends messages to Notifier instance
  	 * @param 	string	Message content
  	 * @param		int			Message type (NOTIFICATION_ERROR, NOTIFICATION_WARNING, NOTIFICATION_NOTICE, NOTIFICATION_SUCCESS)
  	 **/

  	function say ($message, $type = NULL) {
  		@require_once 'M/Notifier.php';
  		if(class_exists('Notifier')){
  			$not=Notifier::getInstance();
  			$not->broadCastMessage($message , $type);
  		}
  	}
    // =============================
    // = returns primary key value =
    // =============================
    public function pk()
    {
      $k = $this->keys();
      return $this->{$k[0]};
    }
    // ===========================================================
    // = Check wether the current record has a primary key value =
    // ===========================================================
    public function isNew()
    {
      $p = $this->pk();
      return empty($p);
    }
    // ===============================
    // = Transaction-related proxies =
    // ===============================
    public function begin()
    {
      $this->getDatabaseConnection()->query('begin');
    }
    public function commit()
    {
      $this->getDatabaseConnection()->query('commit');
    }
    public function rollback()
    {
      $this->getDatabaseConnection()->query('rollback');
    }    
}