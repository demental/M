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


class DB_DataObject_Pluggable extends DB_DataObject implements Iterator {

  protected $_listeners = array();

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
   * @return events return : fail, bypass or an object containing the return value.
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
      if(!is_object($result)) {
        switch($result) {
          case 'fail':return 'fail';break;
          case 'bypass':$finalresult='bypass';break;
        }
      } else {
        return $result;break;
      }
    }
    return $finalresult;
    
  }
  /**
   * Overload => transform it to an event call
   * 
   */
  public function __call($method,$args)
  {
    $res = $this->trigger($method,$args);
    if($res->status=='return') return $res->return;
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
	public function count($countWhat = false,$whereAddOnly = false){
		$this->trigger('count');
		return parent::count($countWhat,$whereAddOnly);
	}
	
	public function delete()
	{
		$result = $this->trigger('delete');
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
  			$not->broadCastMessage($this,$message,$type);
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
    // =============================
    // = returns primary key field name =
    // =============================
    public function pkName()
    {
      $k = $this->keys();
      return $k[0];
    }
    // ===========================================================
    // = Check wether the current record has a primary key value =
    // ===========================================================
    public function isNew()
    {
      $p = $this->pk();
      return empty($p);
    }
    // =====================================
    // = Transaction-related proxy methods =
    // =====================================
    public function begin()
    {
      $this->getDatabaseConnection()->query('begin');
      $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
      $options['transactionRunning'] = 1;
    }
    public function commit()
    {
      $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
      $this->getDatabaseConnection()->query('commit');
      $options['transactionRunning'] = 0;
    }
    public function rollback()
    {
      $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
      $this->getDatabaseConnection()->query('rollback');
      $options['transactionRunning'] = 0;
    }    
    public function transactionRunning()
    {
      $options = PEAR::getStaticProperty('DB_DataObject', 'options');
      return $options['transactionRunning']==1?true:false;
    }
}