<?php
// ======================================
// = Plugin extension for DB_DataObject =
// ======================================

require_once 'DB/DataObject.php';
require_once 'M/DB/DataObject/Iterator.php';
require_once 'M/Config.php';



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

class DB_DataObject_Pluggable extends DB_DataObject_Iterator {

	protected $_plugins = array();

	protected $_pluginInfos = array();
	public $crossLinks_advmultiselect=array();

	public $fb_requiredRuleMessage=REQUIRED_RULE_MESSAGE;
	public $fb_ruleViolationMessage=VIOLATION_RULE_MESSAGE;
	public $fb_linkNewValueText=NEWVALUE_TEXT;
  public $fb_dateFromDatabaseCallback='date2array';


  // =====================================================================================
  // = Automagically loads all needed plugins, based on the properties of the dataObject =
  // = @access public
  // =====================================================================================
	public function _loadPlugins() {
		$pluginInfos=$this->getInstalledPlugins();
		foreach($pluginInfos as $aPluginInfos) {
			if(isset($this->$aPluginInfos['var'])){
				$this->_loadPlugin($aPluginInfos);

			}
		}
		$this->_pluginsLoaded = true;
	}
  // =====================================================================
  // = Loads a plugin , provided the information array about this plugin =
  // = @access protected
  // = @param array $plugininfo information associative array including the following keys :
  // =  * include_file = path to the plugin file relative to the defined plugin dir
  // =  * class_name = class name of the plugin
  // =  * name = identifier name of the plugin 
  // =====================================================================
	protected function _loadPlugin($infos) {
		require_once PLUGIN_DIR.$infos['include_file'];
		$plugin = & new $infos['class_name'];
		$this->_plugins[$infos['name']] = & $plugin;
	}
  // ================================================
  // = Loads a plugin, provided its identifier name
  // = @access public
  // = @param string $pname name of the plugin
  // ================================================
	public function loadPlugin($pname) {
    if(!$this->_plugins[$pname]) {
  		$pluginInfos=$this->getInstalledPlugins();
      foreach($pluginInfos as $aPluginInfo) {
        if($aPluginInfo['name']==$pname) {
          $this->_loadPlugin($aPluginInfo);                    
        }
      }
    }
  }
  /**
  * unloads a plugin, provided its identifier name
  * @access public
  * @param string $pname name of the plugin to unload
  **/

	public function unloadPlugin($pname) {
	    unset($this->_plugins[$pname]);
  }
  /**
  * Unloads all loaded plugins
  * @access public
  **/
  public function unloadPlugins()
  {

    $this->_plugins = array();
    $this->_pluginsLoaded = false;
  }
  /**
  * returns as an array the list of all available plugins
  * @access protected 
  * @return array array of information for available plugins
  **/
	protected function getInstalledPlugins() {
		if(count($this->_pluginInfos)>0) {
			return $this->_pluginInfos;
		} else {
			return array(
            			  array(  'name'=>'wiki',
            			  'include_file'=>'Wiki.php',
            				'class_name'=>'DB_DataObject_Plugin_Wiki',
            				'var'=>'wikiFields'
            				),

  									array(  'name'=>'images',
  									'include_file'=>'Images.php',
  									'class_name'=>'DB_DataObject_Plugin_Images',
  									'var'=>'photoFields'
  									),
									
									
  									array(  'name'=>'upload',
  									'include_file'=>'Upload.php',
  									'class_name'=>'DB_DataObject_Plugin_Upload',
  									'var'=>'uploadFields'
  									),
									
									  array(  'name'=>'ownership',
									  'include_file'=>'Ownership.php',
									  'class_name'=>'DB_DataObject_Plugin_Ownership',
									  'var'=>'ownerShipField'
									  ),
									  array(    'name'=>'user',
									  'include_file'=>'User.php',
									  'class_name'=>'DB_DataObject_Plugin_User',
									  'var'=>'userFields'
									  ),
              			array(	'name'=>'international',
              			'include_file'=>'i18n.php',
    								'class_name'=>'DB_DataObject_Plugin_I18n',
    								'var'=>'i18nFields'
    								),
              			array(	'name'=>'international',
              			'include_file'=>'International.php',
    								'class_name'=>'DB_DataObject_Plugin_International',
    								'var'=>'internationalFields'
    								),
									  array(  'name'=>'tree',
									  'include_file'=>'Tree.php',
									  'class_name'=>'DB_DataObject_Plugin_Tree',
									  'var'=>'treeFields'
									  ),
                    array(  'name'=>'rich',
                    'include_file'=>'Rich.php',
                    'class_name'=>'DB_DataObject_Plugin_Rich',
                    'var'=>'richFields'
                    ),
    								array(  'name'=>'officePack',
    								'include_file'=>'OfficePack.php',
    								'class_name'=>'DB_DataObject_Plugin_OfficePack',
    								'var'=>'officePack'),
    								array(  'name'=>'pager',
    								'include_file'=>'Pager.php',
    								'class_name'=>'DB_DataObject_Plugin_Pager',
    								'var'=>'pagerplugin')

									);
			// TODO Parse a plugins definition file instead of hardcoding the information array here
		}
		return array();
	}

    /**
     * @access protected
     **/
     protected function _getArrayFromPlugins($callback,$params = null) {
         $res = array();
     	if(!$this->_pluginsLoaded) {
     	    $this->_loadPlugins();
     	}	
 		if(is_array($params)) {
 			$params[]=&$this;
 		} else {
 			$params=array(&$this);
 		}

 		foreach($this->_plugins as $plugin) {
 			$subres = call_user_func_array(array($plugin,$callback),$params);
            if(is_array($subres)) {
                $res = array_merge($res,$subres);
            }
 		}
        return $res;
     }
	/**
	 * @access public 
	 * (for specific hacks... might be better to get it as a protected method)  
	 **/
	public function _executePlugins($callback,$params=null) {
    	if(!$this->_pluginsLoaded) {
    	    $this->_loadPlugins();
    	}	
		if(is_array($params)) {
			$params[]=&$this;
		} else {
			$params=array(&$this);
		}
		$res=true;		
		foreach($this->_plugins as $plugin) {
				$tres =  call_user_func_array(array($plugin,$callback),$params);
        if($tres===false) {
          $res = false;
        }
		}
		return $res;
	}
	/**
	 * Returns a reference for a plugin, provided its identifier name
	 * @access public
	 * @param string $pname name of the requested plugin
	 * @return false if the plugin does not exist in the current object
	 * @return Object plugin reference
	 **/
	public function &getPlugin($pname) {
    	if(!$this->_pluginsLoaded) {
    	    $this->_loadPlugins();
    	}
	  if(!key_exists($pname,$this->_plugins)) {
          return false;
	  } else {
	    return $this->_plugins[$pname];
	  }    
	}
	/**
	 * Overload
	 **/
/*	function _call($method,$params,&$return) {
		if(parent::_call($method,$params,$return)) {
			return true;
		} else {
			$this->executePlugins($method,$params,$return);
		}
	}
	*/
/**
 * End plugin management
 **/




// =======================================================
// = DB_DataObject methods override - plugins executions =
// =======================================================    
	function preGenerateForm(&$fb){
		$this->_executePlugins('preGenerateForm',array(&$fb));
		
		$fb->populateOptions();
/**
 * crosslinks => auto show crosslinks when no fb_fieldsToRender is set
 **/
		if(empty($this->fb_submitText)){
			$this->fb_submitText=__("Valider");
		}
	}
	
	function postGenerateForm(&$form,&$fb){

		$this->_executePlugins('postGenerateForm',array(&$form,&$fb));

			/**
			 * crosslink advmultiselect fields
			 * TODO move this to a plugin
			 **/
			if(count($this->crossLinks_advmultiselect)>0){
				foreach($this->crossLinks_advmultiselect as $k){
					$field=$fb->elementNamePrefix.$k.$fb->elementNamePostfix;
					if($form->elementExists($field)){
						HTML_QuickForm::registerElementType('advmultiselect','HTML/QuickForm/advmultiselect.php','HTML_QuickForm_advmultiselect');
						$current=& $form->getElement($field);
						if(!PEAR::isError($current)){
							$label=$current->_label;
							if(!is_array($label)){
								$label=array($label,'note'=>'éléments sélectionnés = bloc de droite');
							}
							$_cros=& HTML_QuickForm::createElement('advmultiselect',$field,$current->_label);
							$_cros->_options=$current->_options;
							if(is_array($this->fb_fieldAttributes) && key_exists($k, $this->fb_fieldAttributes)) {
								$attribs = $this->fb_fieldAttributes[$k];
							} else {
								$attribs=array('style'=>'width:300px');
							}
							$_cros->updateAttributes($attribs);
							$_cros->setSelected($current->getSelected());
							$current=$_cros;
						}

					}
				}
			}
		$form->setJsWarnings(__("Les champs suivants ne sont pas remplis correctement"),__("Merci de les corriger"));
		$form->setRequiredNote(__("Champs obligatoires"));
	}
	
	function prepareLinkedDataObject(&$linkedDataObject, $field){
		$this->_executePlugins('prepareLinkedDataObject',array(&$linkedDataObject,$field));
	}
	function preProcessForm(&$v,&$fb){
		$this->_executePlugins('preProcessForm',array(&$v,&$fb));
	    // On renseigne les prefixe et postfixe localement pour pouvoir récupérer par la suite les fichiers images et upload
	    $this->fb_elementNamePrefix=$fb->elementNamePrefix;
	    $this->fb_elementNamePostfix=$fb->elementNamePostfix;
	}
	function postProcessForm(&$v,&$fb){
		$this->_executePlugins('postProcessForm',array(&$v,&$fb));
	}				
	function insert(){
	  $this->getDatabaseConnection()->query('set names utf8');
		$this->_executePlugins('insert');
        if(parent::insert()) {
    		$this->_executePlugins('postinsert');
            return true;
        }
        return false;
    }
    function get($k = null,$v = null) {
        if(is_array($k)) {
            $k = implode(',',$k);
            $this->whereAdd('id IN('.$k.')');
            return $this->find();
        } else {
            return parent::get($k,$v);
        }
    }
	function update($do = false){
    $this->getDatabaseConnection()->query('set names utf8');
		$this->_executePlugins('update');
        if(parent::update($do)!==false) {
    		$this->_executePlugins('postupdate');
            return true;
        }
        return false;		
	}

	function fetch(){
		$this->_executePlugins('prefetch');
		if(parent::fetch()){
  		$this->_executePlugins('postfetch');
			return true;
		}
		return false;
	}

	function find($autoFetch=false){
    $this->getDatabaseConnection()->query('set names utf8');
		$this->_executePlugins('find',array($autoFetch));
		return parent::find($autoFetch);
	}
	function query($req){
    $this->getDatabaseConnection()->query('set names utf8');
    return parent::query($req);
  }
	function count(){
		$this->_executePlugins('count');
		return parent::count();
	}
	
	function delete()
	{
		$res = $this->_executePlugins('delete');
    if($res===false) {
      return true;
    }
    if(parent::delete()) {
		$this->_executePlugins('postdelete');
        return true;
    }
    return false;
  }
		
    function dateOptions($field, &$fb) {
			$this->_executePlugins('dateOptions',array($field,$fb));
      return array('format' => 'd-m-Y','addEmptyOption'=>true,'emptyOptionText'=>array('Y'=>'YYYY','m'=>'mm','d'=>'dd'));
    }
    function deleteLinks() {
        $args=func_get_args();
        foreach($args as $tbl){
            $this->say('Suppression données connexes pour '.$tbl);

            $do=& DB_dataObject::factory($tbl);
            $links=$do->links();
            foreach ($links as $field=>$link) {
                if(preg_match('`^'.$this->tableName().':.+$`',$link,$match)){
                    $do->$field=$this->id;// TODO getprimarykey
                    break;
                }
            }
            while($do->fetch()){
                $do->delete();
                $this->say('Suppression donnée connexe '.$do->tableName().' '.$do->id);
            }
        }    
    }
    function getSingleMethods($base = null) {
        if(is_null($base) || $base===false) {
            $base = array();
        }
        $res = $this->_getArrayFromPlugins('getSingleMethods');
        if(is_array($res)) {
            return array_merge($base,$res);
        } else {
            return $base;
        }
    }
    function getGlobalMethods($base = null) {
        if(is_null($base) || $base===false) {
            $base = array();
        }
        $res = $this->_getArrayFromPlugins('getGlobalMethods');
//        $res['utf8_convert'] = array('title'=>'Conversion UTF8');
        
        if(is_array($res)) {
            return array_merge($base,$res);
        } else {
            return $base;
        }
    }
    function getBatchMethods($base = null) {
        if(is_null($base) || $base===false) {
            $base = array();
        }
        $res = $this->_getArrayFromPlugins('getBatchMethods');
        if(is_array($res)) {
            return array_merge($base,$res);
        } else {
            return $base;
        }
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
/*    public function _query($string)
    {
      $res = parent::_query($string);
      $this->__last_query = $this->getDatabaseConnection()->last_query;
      return $res;
    }*/
    
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
    // =======================================================================================
    // = Temporary use, therefore deprecated. Was used to convert old iso-encoded databases  =
    // =======================================================================================
    public function utf8_convert()
    {
      parent::find();
      while(parent::fetch()) {
        $this->getDatabaseConnection()->query('set names utf8');
        parent::update();
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