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
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


if(!defined('PLUGIN_DIR')) {
	define('PLUGIN_DIR','M/DB/DataObject/Plugin/');
}

if(!function_exists('__')){
  T::getLang();
}


function date2array($dt){
    if ( $dt==NULL )
    {
        return array('Y'=>'0000','m'=>'00','d'=>'00');
    }
    else
    {
        return DB_DataObject_FormBuilder::_date2array($dt);
    }
}


class DB_DataObject_Pluggable extends DB_DataObject implements Iterator {

  protected $_listeners = array();


  public static $objectRegistry = array();


  public $fb_dateFromDatabaseCallback='date2array';
  public $_pluginsLoaded = false;

  /**
   * retreive from registery and inject data if object is provided
   * avoid it would be better
   */
  public static function &retreiveFromRegistry($tablename,$pk,$v = null,$inject = null) {
    if(!is_null($v)) {
      $keystore = $tablename.'__keystore_'.$pk;
      $value = $v;
    } else {
      $keystore = $tablename;
      $value = $pk;
    }

    if(key_exists($keystore,DB_DataObject_Pluggable::$objectRegistry)
    && key_exists($value,DB_DataObject_Pluggable::$objectRegistry[$keystore])) {
      $ret = DB_DataObject_Pluggable::$objectRegistry[$keystore][$value];

      if(is_object($inject)) {

        $inject->setFrom($ret->toArray());
        $inject->{$inject->pkName()} = $ret->{$ret->pkName()};
        return true;
      } else {
        return $ret;
      }
    }

    return false;
  }
  public static function clearFromRegistry($tablename,$pk,$v = null  ) {
    if(!is_null($v)) {
      $keystore = $tablename.'__keystore_'.$pk;
      $value = $v;
    } else {
      $keystore = $tablename;
      $value = $pk;
    }
    if(key_exists($keystore,DB_DataObject_Pluggable::$objectRegistry)
    && key_exists($value,DB_DataObject_Pluggable::$objectRegistry[$keystore])) {
      unset(DB_DataObject_Pluggable::$objectRegistry[$keystore][$value]);
    }
  }
  public static function storeToRegistry($object) {
    if(!$object->isCachable()) return;
    DB_DataObject_Pluggable::$objectRegistry[$object->tableName()][$object->pk()] = $object;

    if(is_array($object->__keystore)) {
      foreach($object->__keystore as $keystore) {
        DB_DataObject_Pluggable::$objectRegistry[$object->tableName().'__keystore_'.$keystore][$object->{$keystore}] = $object;
      }
    }
  }
  public function isCachable()
  {
    return true;
  }
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

	/**
	 * Set a DOclass as a linked obj.
	 * @param DB_DataObject_Pluggable $obj linked record (must reside in database)
	 * @param string foreign key name (automatically set if not provided)
	 */
	public function setLinkObj(DB_DataObject_Pluggable $linkObj,$field=null)
	{
		if(is_null($field)) {
			$field = $linkObj->tableName().'_id';

		}
		$placeholder = '_'.$field.'_obj';
		$this->{$field} = $linkObj->id;
		$this->{$placeholder} = $obj;
	}

  /**
   * Office app specific methods
   * @todo should be removed from DAO, added somewhere else closer to controller layer
   */
  public function prepareSearchForm($fb)
  {
    $this->trigger('prepareSearchForm', array($fb));
  }


  public function can_add_with_parameters($params)
  {
    return true;
  }
  /**
   * instead of using this as an iterable object, you can call all()
   * to get on array of all the records, allowing to call some array manipulations
   * like sort() or count()
   * @returns array
   */
  public function all()
  {
    $return = array();
    while($this->fetch()) {
      $return[]=clone($this);
    }
    return $return;
  }
	/**
   * Reloads the content of the object from the database
   * @warning relies on setFrom() and toArray()
   */
	public function reload()
	{
		$obj2 = DB_DataObject::factory($this->tableName());
		$obj2->{$this->pkName()} = $this->pk();
		$obj2->find(true);
		$this->setFrom($obj2->toArray());
    return $this;
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
   * @param optional custom parameteres for plugin (otherwise the params defines in _getPluginsDef will be loaded)
   **/
	public function loadPlugin($pluginName,$params = null) {
    if(is_null($params)) {
      $defs = $this->_getPluginsDef();
      $params = $defs[$pluginName];
    }
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
      $this->removeListener(PluginRegistry::getInstance($pluginName,'DB'));
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
	public function getPlugin($pname)
	{
  	if(!$this->_pluginsLoaded) {
  	    $this->loadPlugin($pname);
  	}
    return PluginRegistry::getInstance($pname,'DB');
	}

	public function hasPlugin($pname)
	{
    $def =$this->_getPluginsDef();
    if(array_key_exists($pname,$def)) return true;
    if(in_array(PluginRegistry::getInstance($pname,'DB'), $this->_listeners)) return true;
    return false;
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
    if(!$this->_pluginsLoaded) {
      $this->_loadplugins();
    }
    $finalresult = null;

    foreach($this->_listeners as $listener) {
      $result = $listener->handleEvent($this,$eventName,$params);
      if(!is_object($result)) {
        switch(true) {
          case $result === 'fail':return 'fail';break;
          case $result === 'bypass': $finalresult='bypass';break;
          case $result === 'not_handled': break;
          default: $finalresult = true;break;
        }
      } else {
        return $result;break;
      }
    }
    return $finalresult;

  }
  /**
   * This type of trigger allows to return an altered version of $params
   * Should work only with ONE parameter in $params !
   * @param string name of the event
   * @param mixed parameters passed to the events
   * @return events return : fail, bypass or an object containing the return value.
   */
  public function triggerAndAlter($eventName,$params = null)
  {
    $eventName = strtolower($eventName);
    if(!$this->_pluginsLoaded) {
      $this->_loadplugins();
    }
    $finalresult = null;

    foreach($this->_listeners as $listener) {
      $result = $listener->handleEvent($this,$eventName,$params);
      if(!is_object($result)) {
        switch(true) {
          case $result === 'fail':return 'fail';break;
          case $result === 'bypass':$finalresult='bypass';break;
        }
      } else {
        $params = array($result->return);
      }
    }
    return $params[0];

  }

  /**
   * Overload => transform it to an event call
   *
   */
  public function __call($method,$args)
  {
    $res = $this->trigger($method,$args);
    if($res->status == 'return') return $res->return;
    if($res === false || is_null($res)) throw new NoMethodException('No method '.$method.' for '.get_class($this));
  }

###################### End plugin management #######################
  /**
   * Adds ability to get records by an array of primary keys
   */
  public function get($k = null,$v = null) {
    if(is_array($k)) {
        $k = $this->sqlin($k);
        $this->whereAdd('id IN('.$k.')');
        return $this->find();
    } else {
        if(is_null($v) || $this->__keystore == $k) {
          if(!$ret = DB_DataObject_Pluggable::retreiveFromRegistry($this->tableName(),$k,$v,$this)) {
            if($ret=parent::get($k,$v)) {
              DB_DataObject_Pluggable::storeToRegistry($this);
              return $ret;
            }
          }
          return $ret;
        }
        return parent::get($k,$v);
    }
  }

  /**
   * Mimics ActiveRecord find : throws an exception if not found (or no criteria)
   * @return void
   * all these example work :
   * $a->get_or_die(123);
   * $a->get_or_die('id', 123);
   * $a->get_or_die(null, 123);
   * $a->get_or_die('ref', 'ART123');
   */
	public function get_or_die($id_or_field, $id = '...') {
    if(empty($id)) {
      throw new NotFoundException(__('error.dataobject_fetch_without_an_id', array(__("modules.{$this->tableName()}.frontname"))));
    }
    $id = $id == '...' ? null : $id;
    if(empty($id_or_field)) {
      $id_or_field = $id;
      $id = null;
    }

    if(!$this->get($id_or_field, $id)) {
      if($id) {
        throw new NotFoundException(__('error.dataobject_notfound_byfield', array($id_or_field, $id, __("modules.{$this->tableName()}.frontname"))));
      } else {
        throw new NotFoundException(__('error.dataobject_notfound_byid', array($id_or_field, __("modules.{$this->tableName()}.frontname"))));
      }
    }
  }

  public function find_or_die($autoFetch = false)
  {
    if(!$this->find($autoFetch)) throw new NotFoundException(__('error.dataobject_notfound'));
  }

  /**
   * Delete linked records
   * @param $param1,$param2,...,$paramN strings foreign table names
   */
  function deleteLinks() {
    $args=func_get_args();
    foreach($args as $tbl){
      $this->say(__('Deleting foreign records in %s',array($tbl)));
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
        $this->say(__('Deleting %s %s',array($do->tableName(), $do->id)));
      }
    }
  }

// =======================================================
// = DB_DataObject methods override - events triggered   =
// =======================================================

  function postPrepareSearchForm(&$form,&$fb){
	  $this->trigger('postPrepareSearchForm',array(&$form,&$fb));
  }
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

  public function save()
  {
    $pk = $this->pk();
    if(empty($pk) || $this->__new_with_id) {
      $this->insert();
    } else {
      $this->update();
    }
  }
	function insert(){

		$result = $this->trigger('insert');

		switch(true) {
		  case $result === 'bypass':
    		    return true;
		    break;
		  case $result === 'fail':
		    return false;
		    break;
		  default:
        if(parent::insert()) {

          DB_DataObject_Pluggable::storeToRegistry($this);
          $this->trigger('postinsert');
          return true;
        }
        return false;
    }
  }
	function update($do = false){

		$result = $this->trigger('update',array($do));
    switch(true) {
      case $result === 'bypass':
        return true;
        break;
      case $result === 'fail':
        return false;
        break;
      default:
      if($this->_update($do)!==false) {
        $this->trigger('postupdate');
        return true;
      }
      return false;
    }
	}

	function fetch(){
		$this->trigger('prefetch');
		if(parent::fetch()){
      $this->_memoized = array();
      $this->trigger('postfetch');
      return true;
		}
		return false;
	}
  function _query($string)
  {
    if(!defined('MDB2_UTF8_NAMES_SET')) {
      $db = $this->getDatabaseConnection();
      $db->_doQuery('set names utf8', false, $db->getConnection(), $db->database_name);

      define('MDB2_UTF8_NAMES_SET',true);
    }
    return parent::_query($string);
  }
	function find($autoFetch=false){
		$this->trigger('find',array($autoFetch));
		return parent::find($autoFetch);
	}
	function query($req){

		$this->trigger('query',array($req));
    return parent::query($req);
  }
	public function count($countWhat = false,$whereAddOnly = false){
		$this->trigger('count');
		return parent::count($countWhat,$whereAddOnly);
	}

	/**
	 * Use ObjectRegistry
	 */
	public function getLink($row, $table = null, $link = false)
  {
//    return parent::getLink($row,$table,$link);
    if ($table === null) {
        $links = $this->links();

        if (is_array($links)) {

            if ($links[$row]) {
                list($table,$link) = explode(':', $links[$row]);
                if ($p = strpos($row,".")) {
                    $row = substr($row,0,$p);
                }
                return $this->getLink($row,$table,$link);

            }

            // $this->raiseError(
            //     "getLink: $row is not defined as a link (normally this is ok)",
            //     DB_DATAOBJECT_ERROR_NODATA);

            $r = false;
            return $r;// technically a possible error condition?

        }
        // use the old _ method - this shouldnt happen if called via getLinks()
        if (!($p = strpos($row, '_'))) {
            $r = null;
            return $r;
        }
        $table = substr($row, 0, $p);
        return $this->getLink($row, $table);


    }



    if (!isset($this->$row)) {
        // $this->raiseError("getLink: row not set $row", DB_DATAOBJECT_ERROR_NODATA);
        return false;
    }
    if($obj = DB_DataObject_Pluggable::retreiveFromRegistry($table,$this->{$row})) {

      return $obj;
    }
    $obj = parent::getLink($row,$table,$link);
    if(is_object($obj)) {
      DB_DataObject_Pluggable::storeToRegistry($obj);
    }
    return $obj;
  }

  public function memoized_link($link_field, $fallback = null )
  {
    if(!$this->_memoized[$link_field] instanceOf DB_DataObject) {
      $this->_memoized[$link_field] = $this->getLink($link_field);
      if(!$this->_memoized[$link_field] && $fallback instanceOf Closure) {
        $this->_memoized[$link_field] = $fallback($this);
      }
    }
    return $this->_memoized[$link_field];
  }

  public function memoized_reverse_link($model, $link_field, $reload = false)
  {
    $key = "{$model}:{$link_field}";
    if($reload) $this->_memoized[$key] = null;
    if(!$this->_memoized[$key] instanceOf DB_DataObject) {
      $this->_memoized[$key] = DB_DataObject::factory($model);
      $pk = $this->pk();
      if($pk) {
        $this->_memoized[$key]->$link_field = $pk;
      } else {
        $this->_memoized[$key]->whereAdd('1=2');
      }
      $this->_memoized[$key]->find();
    }
    return $this->_memoized[$key];
  }

	public function delete()
	{
		$result = $this->trigger('delete');
		switch(true) {
		  case $result === 'bypass':
        $this->clearFromRegistry($this->tableName(), $this->pk());
		    return true;
		    break;
		  case $result === 'fail':
		    return false;
		    break;
		  default:
      if(parent::delete()!==false) {
        $this->clearFromRegistry($this->tableName(), $this->pk());
        $this->trigger('postdelete');
        return true;
      }
      return false;
    }
  }

  public function dateOptions($field, &$fb) {
		$this->trigger('dateOptions',array($field,$fb));
    return array('format' => 'd-m-Y','addEmptyOption'=>true,'emptyOptionText'=>array('Y'=>'YYYY','m'=>'mm','d'=>'dd'),'maxYear'=>date('Y')+2);
  }

  public function getSingleMethods($base = null) {
    if(!is_array($base)) {
        $base = array();
    }
    $base = $this->triggerAndAlter('getSingleMethods',array($base));
    return $base;
  }
  public function getGlobalMethods($base = null) {
    if(!is_array($base)) {
      $base = array();
    }
    $base = $this->triggerAndAlter('getGlobalMethods',array($base));
    return $base;
  }
  public function getBatchMethods($base = null) {
    if(!is_array($base)) {
      $base = array();
    }

    $base = $this->triggerAndAlter('getBatchMethods',array($base));
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

    /**
     * returns prefered link field if this is a n-n table.
     */
    public function isNtable()
    {
      return false;
    }

    // =====================================
    // = Transaction-related proxy methods =
    // =====================================
    public function begin()
    {
      if($this->transactionRunning()) {
        return;
      }
      $this->getDatabaseConnection()->query('begin');
      $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
      $options['transactionRunning'] = 1;
    }
    public function commit()
    {
      if(!$this->transactionRunning()) {
        return;
      }
      $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
      $this->getDatabaseConnection()->query('commit');
      $options['transactionRunning'] = 0;
    }
    public function rollback()
    {
      if(!$this->transactionRunning()) {
        return;
      }
      $options = & PEAR::getStaticProperty('DB_DataObject', 'options');
      $this->getDatabaseConnection()->query('rollback');
      $options['transactionRunning'] = 0;
    }
    public function transactionRunning()
    {
      $options = PEAR::getStaticProperty('DB_DataObject', 'options');
      return $options['transactionRunning']==1?true:false;
    }

    /**
     * Updates  current objects variables into the database
     * DB_DataObject fix to allow update of objects that were fetched from a join query.
     *
     */
    function _update($dataObject = false)
    {
        global $_DB_DATAOBJECT;
        // connect will load the config!
        $this->_connect();


        $original_query =  $this->_query;

        $items =  isset($_DB_DATAOBJECT['INI'][$this->_database][$this->__table]) ?
            $_DB_DATAOBJECT['INI'][$this->_database][$this->__table] : $this->table();

        // only apply update against sequence key if it is set?????

        $seq    = $this->sequenceKey();
        if ($seq[0] !== false) {
            $keys = array($seq[0]);
            if (!isset($this->{$keys[0]}) && $dataObject !== true) {
                $this->raiseError("update: trying to perform an update without
                        the key set, and argument to update is not
                        DB_DATAOBJECT_WHEREADD_ONLY
                    ", DB_DATAOBJECT_ERROR_INVALIDARGS);
                return false;
            }
        } else {
            $keys = $this->keys();
        }
        $pkName = $keys[0];
        $pkVal = $this->{$keys[0]};

        if (!$items) {
            $this->raiseError("update:No table definition for {$this->__table}", DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return false;
        }
        $datasaved = 1;
        $settings  = '';
        $this->_connect();

        $DB            = &$_DB_DATAOBJECT['CONNECTIONS'][$this->_database_dsn_md5];
        $dbtype        = $DB->dsn["phptype"];
        $quoteIdentifiers = !empty($_DB_DATAOBJECT['CONFIG']['quote_identifiers']);
        $options = $_DB_DATAOBJECT['CONFIG'];


        $ignore_null = !isset($options['disable_null_strings'])
                    || !is_string($options['disable_null_strings'])
                    || strtolower($options['disable_null_strings']) !== 'full' ;


        foreach($items as $k => $v) {

            if (!isset($this->$k) && $ignore_null) {
                continue;
            }
            // ignore stuff thats

            // dont write things that havent changed..
            if (($dataObject !== false) && isset($dataObject->$k) && ($dataObject->$k === $this->$k)) {
                continue;
            }

            // - dont write keys to left.!!!
            if (in_array($k,$keys)) {
                continue;
            }

             // dont insert data into mysql timestamps
            // use query() if you really want to do this!!!!
            if ($v & DB_DATAOBJECT_MYSQLTIMESTAMP) {
                continue;
            }


            if ($settings)  {
                $settings .= ', ';
            }

            $kSql = ($quoteIdentifiers ? $DB->quoteIdentifier($k) : $k);

            if (is_a($this->$k,'DB_DataObject_Cast')) {
                $value = $this->$k->toString($v,$DB);
                if (PEAR::isError($value)) {
                    $this->raiseError($value->getMessage() ,DB_DATAOBJECT_ERROR_INVALIDARG);
                    return false;
                }
                $settings .= "$kSql = $value ";
                continue;
            }

            // special values ... at least null is handled...
            if (!($v & DB_DATAOBJECT_NOTNULL) && DB_DataObject::_is_null($this,$k)) {
                $settings .= "$kSql = NULL ";
                continue;
            }
            // DATE is empty... on a col. that can be null..
            // note: this may be usefull for time as well..
            if (!$this->$k &&
                    (($v & DB_DATAOBJECT_DATE) || ($v & DB_DATAOBJECT_TIME)) &&
                    !($v & DB_DATAOBJECT_NOTNULL)) {

                $settings .= "$kSql = NULL ";
                continue;
            }


            if ($v & DB_DATAOBJECT_STR) {
                $settings .= "$kSql = ". $this->_quote((string) (
                        ($v & DB_DATAOBJECT_BOOL) ?
                            // this is thanks to the braindead idea of postgres to
                            // use t/f for boolean.
                            (($this->$k === 'f') ? 0 : (int)(bool) $this->$k) :
                            $this->$k
                    )) . ' ';
                continue;
            }
            if (is_numeric($this->$k)) {
                $settings .= "$kSql = {$this->$k} ";
                continue;
            }
            // at present we only cast to integers
            // - V2 may store additional data about float/int
            $settings .= "$kSql = " . intval($this->$k) . ' ';
        }


        if (!empty($_DB_DATAOBJECT['CONFIG']['debug'])) {
            $this->debug("got keys as ".serialize($keys),3);
        }
        if ($dataObject !== true) {
            $this->_build_condition($items,$keys);
        } else {
            // prevent wiping out of data!
            if (empty($this->_query['condition'])) {
                 $this->raiseError("update: global table update not available
                        do \$do->whereAdd('1=1'); if you really want to do that.
                    ", DB_DATAOBJECT_ERROR_INVALIDARGS);
                return false;
            }
        }




        if ($settings && isset($this->_query) && $this->_query['condition']) {

            $table = ($quoteIdentifiers ? $DB->quoteIdentifier($this->__table) : $this->__table);
            if($dataObject === DB_DATAOBJECT_WHEREADD_ONLY) {
              $r = $this->_query("UPDATE  {$table}  SET {$settings} {$this->_query['condition']} ");
            } else {
              $pkName = ($quoteIdentifiers ? $DB->quoteIdentifier($pkName):$pkName);
              $pkVal = $DB->quote($pkVal);
              $r = $this->_query("UPDATE  {$table}  SET {$settings} WHERE $pkName = $pkVal");
            }
            // restore original query conditions.
            $this->_query = $original_query;

            if (PEAR::isError($r)) {
                $this->raiseError($r);
                return false;
            }
            if ($r < 1) {
                return 0;
            }
            $this->_clear_cache();
            return $r;
        }
        // restore original query conditions.
        $this->_query = $original_query;

        // if you manually specified a dataobject, and there where no changes - then it's ok..
        if ($dataObject !== false) {
            return true;
        }

        $this->raiseError(
            "update: No Data specifed for query $settings , {$this->_query['condition']}",
            DB_DATAOBJECT_ERROR_NODATA);
        return false;
    }

    /**
     * @params array
     * add a in(....) clause
     */
    public function sqlin($values)
    {
      $db = $this->getDatabaseConnection();
      if (!is_array($values)) {
        $values = explode(',',$values);
        $values = array_map('trim', $values);
      }
      $values = array_map(array($db, 'quote'), $values);

      return implode(',',$values);
    }

    public function raiseError($message, $type = null, $behaviour = null)
    {
			$error = parent::raiseError($message, $type, $behaviour);
      throw new Exception($type . ' ' . $message . ' ' . $error->getUserInfo());
    }
}
