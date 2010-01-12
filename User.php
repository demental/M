<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   User
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

define ('ERROR_WRONG_PASSWORD','1');
define ('ERROR_NO_USER','2');

$errorMessage[ERROR_WRONG_PASSWORD]=__('Invalid password');
$errorMessage[ERROR_NO_USER]=__('This username does not exist');

/**
 * 
 * Web user management
 *
 */
class User{
	public $level;
	public $loggedIn;
	public $_error;
	public $currentContainer;
	public $id;
	protected $context;
	public $prefs=array();
	protected $properties=array();
	public $completeName;
	public $target;
	public $language;
	public $email;
	private static $_instance;
	private function __construct(){
	}
	function __destruct() {
		$this->store();
	}
	function store() {
		if($this->isLoggedIn()) {
			$_SESSION[$this->context]['userId'] = $this->getId();
			$_SESSION[$this->context]['userLanguage']=$this->language;
		}
		$_SESSION[$this->context]['userProperties'] = $this->properties;
		$_SESSION[$this->context]['target'] = $this->target;

	}
	public function hasProperty($prop)
	{
		return $this->getProperty($prop)?TRUE:FALSE;
	}
	function getproperties() {
		return $this->properties;
	}
	function setProperties($prop) {
		$this->properties = $prop;
	}
	function setProperty($prop,$val) {
		$this->properties[$prop] = $val;
	}
	function getProperty($prop) {
		return $this->properties[$prop];
	}
	public static function &getInstances() {
		return self::$_instance;
	}
	public static function getInstance($context='front')
	{
		$ssid=session_id();
		if(empty($ssid)) {
			$sn=session_name();
			if(isset($_GET[$sn])) if(strlen($_GET[$sn])!=32) unset($_GET[$sn]);
			if(isset($_POST[$sn])) if(strlen($_POST[$sn])!=32) unset($_POST[$sn]);
			if(isset($_COOKIE[$sn])) if(strlen($_COOKIE[$sn])!=32) unset($_COOKIE[$sn]);
			if(isset($PHPSESSID)) if(strlen($PHPSESSID)!=32) unset($PHPSESSID);
      
			session_start();
		}

		if (!isset(self::$_instance[$context]))
		{
			self::$_instance[$context] = new User();
			$data = PEAR::getStaticProperty('user','global');

			self::$_instance[$context]->setContext($context);
			self::$_instance[$context]->setSettings($data['containers']);
			self::$_instance[$context]->setLanguage($_SESSION[$context]['userLanguage']);
			self::$_instance[$context]->setId($_SESSION[$context]['userId']);
			self::$_instance[$context]->setProperties($_SESSION[$context]['userProperties']);
			self::$_instance[$context]->setTarget($_SESSION[$context]['target']);			
			self::$_instance[$context]->setLevel($data['defaults']['level']);
			if(!empty($data['defaults']['preInitHook']) && function_exists($data['defaults']['preInitHook'])) {
				call_user_func($data['defaults']['preInitHook'],self::$_instance[$context]);
			}

		}
		return self::$_instance[$context];
	}//fin fonction GetInstance
	function setSettings($settings) {
		if(empty($settings) || !is_array($settings)) {
			throw new Exception('Users are not configured : '.print_r($settings,true));
		}
		$this->containers=$settings;
		 
	}
	function setContext($context) {
		$this->context=$context;
	}
	function getLanguage(){
		return empty($this->language)?false:$this->language;
	}
	function setLanguage($l){
		$this->language=$l;
	}
	function getEmail(){
		if(!$this->isLoggedIn()){
			return false;
		} else {
			return $this->getField('email');
		}
	}
	function getPrefs(){
		$pr=$this->containers[$this->context]['prefs'];
		if($pr) {
			$this->prefs=unserialize($this->currentContainer->$pr);
			if(!is_array($this->prefs)){
				$this->prefs=array();
			}
		}
	}
	function getPref($var){
		if(@key_exists($var,$_SESSION[$this->context]['userPref'])){
			return $_SESSION[$this->context]['userPref'][$var];
		} else {
			return false;
		}
	}
	function setPref($var,$val=NULL){
		// TODO Stockage dans la base
		if(NULL==$val){
			unset($_SESSION[$this->context]['userPref'][$var]);
		} else {
			$_SESSION[$this->context]['userPref'][$var]=$val;
		}
	}
	function getId(){
		return $this->id;
	}
	function setId($id){
		$this->id=$id;
		if(!empty($id)){
			$this->populate($id);
		}
	}
	function getCompleteName(){
		if(key_exists('name',$this->containers[$this->context])){
			$name=$this->containers[$this->context]['name'];
			if(!is_array($name)){
				$name=array($name);
			}
			$out="";
			foreach($name as $k=>$v){
				$out.=$this->currentContainer->$v." ";
			}
		}
		return $out;
	}
	function populate($id){
		if(class_exists('Mreg')) {
			try {
				Mreg::get('setup')->setUpEnv();
			} catch(Exception $e) {

			}
		}
		$this->currentContainer = DB_DataObject::factory($this->containers[$this->context]['table']);
		if(PEAR::isError($this->currentContainer)) {
			ob_clean();
			echo 'populating '.$this->context.' failed ';
			print_r($this);
			exit;
			$op = PEAR::getStaticProperty('DB_DataObject','options');
			var_dump($op);
		}
		$this->currentContainer->get($id);
		$this->getPrefs();
	}
	function getLastVisit(){
		return $this->getPref('lastVisit');
	}

	function getError(){
		return $this->_error;
	}
	function isLoggedIn(){
		return $this->id?TRUE:FALSE;
	}

	function logout(){
		$this->currentContainer=null;
		$this->loggedIn=false;
		$this->level=null;
		$this->id=null;
		unset($_SESSION[$this->context]);
		$this->properties=null;
		if(key_exists(COOKIEVAL,$_COOKIE)){
			unset($_COOKIE[COOKIEVAL]);
		}
	}
	function login($login,$pwd,$persistent=FALSE){
		$error="";
		$pass=FALSE;
		$belong="";
		$dbdo=&DB_DataObject::factory($this->containers[$this->context]['table']);
    $defs = $dbdo->_getPluginsDef();
    $defs = $defs['user'];
		$lg=$defs['login'];
		if(empty($lg)) {
			if(empty($this->context)) {
				if(empty($defs['context'])) {
					throw new Exception('User context was not set in the user ORM context property');
				}
			} else {
				throw new Exception('User login field was not set in the user ORM context property');
			}
		}

		$dbdo->$lg=$login;
		if(!$dbdo->find(TRUE)){
			$error=ERROR_NO_USER;
		} else {
			if($callback = $defs['passEncryption']) {
				$pwd = call_user_func($callback,$pwd);
			}
			if($dbdo->{$defs['pwd']}!=$pwd){
				$error=ERROR_WRONG_PASSWORD;
			} else {
				$error="";
				$pass=TRUE;
			}
		}
		if($pass){
		  $this->forceLogin($dbdo,$defs);
			return true;
		} else {
			$this->_error=$error;
			return false;
		}
	}
	/**
	 * Forces loging in (bypass login/password checking)
	 * @param DB_DataObject the current user database container
	 * @param array user fields definition
	 * @param bool (optional) default true wether to store in database last connection date
	 */
	public function forceLogin(DB_DataObject $dataobject, $fieldDefinition, $storecnxdate=true)
	{
	 	$this->setId($dataobject->pk());
		$this->currentContainer = $dataobject;
		$this->getPrefs();
		if($fieldDefinition['last_cnx']) {
			$this->setProperty('last_cnx',$this->currentContainer->{$fieldDefinition['last_cnx']});
			$this->currentContainer->{$fieldDefinition['last_cnx']}=date('Y-m-d H:i:s');
			$this->currentContainer->update();
		}
		$this->store();
	}
	/**
	 * returns the web path user must be redirected to after loging in
	 */
	function getTarget(){
		return($_SESSION[$this->context]['target']);
	}
	function setTarget($url){
		$this->target=$_SESSION[$this->context]['target']=$url;
	}
	function clearTarget() {
		$this->target=$_SESSION[$this->context]['target']=false;
	}
	function getLevel(){
		return $this->level;
	}
	function setLevel($level) {
		$this->level = $level;
	}

	function setField($field,$value){
		if($this->isLoggedIn()){
			$this->currentContainer->$field=$value;
			$this->currentContainer->update();
		} else {
			return false;
		}
	}
	function reloadContainer(){
		require_once 'DB/DataObject.php';
		if(class_exists('Mreg')) {
			try {
				Mreg::get('setup')->setUpEnv();
			} catch(Exception $e) {

			}
		}
		$this->currentContainer=NULL;
		$this->currentContainer=& DB_DataObject::factory($this->containers[$this->context]['table']);
		$this->currentContainer->get($this->getId());
	}
	function &getDBDO() {
		$this->reloadContainer();
		return $this->currentContainer;
	}
	function getNom() {
		return $this->currentContainer->__toString();
	}
	function getField($field){
		if($this->isLoggedIn()){
			return $this->currentContainer->$field;
		} else {
			return false;
		}
	}
}