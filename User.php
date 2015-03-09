<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   User
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

define ('ERROR_WRONG_PASSWORD','1');
define ('ERROR_NO_USER','2');

/**
 *
 * Web user management
 *
 */
class User {
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
  public function __destruct() {
		$this->store();
	}
  public function store() {
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
  public function getproperties() {
		return $this->properties;
	}
  public function setProperties($prop) {
		$this->properties = $prop;
	}
  public function setProperty($prop,$val) {
		$this->properties[$prop] = $val;
	}
  public function getProperty($prop) {
		return $this->properties[$prop];
	}
	public static function &getInstances() {
		return self::$_instance;
	}
	public static function getInstance($context='front')
	{
		if (php_sapi_name() != "cli") {
			$ssid=session_id();
			if(empty($ssid)) {
				$sn=session_name();
				if(isset($_GET[$sn])) if(strlen($_GET[$sn])!=32) unset($_GET[$sn]);
				if(isset($_POST[$sn])) if(strlen($_POST[$sn])!=32) unset($_POST[$sn]);
				if(isset($_COOKIE[$sn])) if(strlen($_COOKIE[$sn])!=32) unset($_COOKIE[$sn]);
				if(isset($PHPSESSID)) if(strlen($PHPSESSID)!=32) unset($PHPSESSID);

				session_start();
			}
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
			if(!empty($data['defaults']['preInitHook']) && function_exists($data['defaults']['preInitHook'])) {
				call_user_func($data['defaults']['preInitHook'],self::$_instance[$context]);
			}

		}
		return self::$_instance[$context];
	}//fin fonction GetInstance
  public function setSettings($settings) {
		if(empty($settings) || !is_array($settings)) {
			throw new Exception('Users are not configured : '.print_r($settings,true));
		}
		$this->containers=$settings;

	}
  public function setContext($context) {
		$this->context=$context;
	}
  public function getLanguage(){
		return empty($this->language)?false:$this->language;
	}
  public function setLanguage($l){
		$this->language=$l;
	}
  public function getEmail(){
		if(!$this->isLoggedIn()){
			return false;
		} else {
			return $this->getField('email');
		}
	}
  public function getPrefs(){
		$pr=$this->containers[$this->context]['prefs'];
		if($pr) {
			$this->prefs=unserialize($this->currentContainer->$pr);
			if(!is_array($this->prefs)){
				$this->prefs=array();
			}
		}
	}
  public function getPref($var){
		if(@key_exists($var,$_SESSION[$this->context]['userPref'])){
			return $_SESSION[$this->context]['userPref'][$var];
		} else {
			return false;
		}
	}
  public function setPref($var,$val=NULL){
		// TODO Stockage dans la base
		if(NULL==$val){
			unset($_SESSION[$this->context]['userPref'][$var]);
		} else {
			$_SESSION[$this->context]['userPref'][$var]=$val;
		}
	}
  public function getId(){
		return $this->id;
	}
  public function setId($id){
		$this->id=$id;
		if(!empty($id)){
			$this->populate($id);
		}
	}
  public function getCompleteName(){
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
  public function populate($id){
		if(class_exists('Mreg')) {
			try {
				Mreg::get('setup')->setUpEnv();
			} catch(Exception $e) {

			}
		}
		$this->currentContainer = DB_DataObject::factory($this->containers[$this->context]['table']);
		if(PEAR::isError($this->currentContainer)) {
			throw new Exception($this->currentContainer->getMessage());
		}
		$this->currentContainer->get($id);
    if(method_exists($this->currentContainer, 'onUp')) {
      $this->currentContainer->onUp();
    }
    $this->getPrefs();
	}
  public function getLastVisit(){
		return $this->getPref('lastVisit');
	}

  public function getError(){
		return $this->_error;
	}
  public function isLoggedIn(){
		return $this->id?TRUE:FALSE;
	}

  public function logout(){
    $this->currentContainer->onSignout();
		$this->currentContainer=null;
		$this->loggedIn=false;
		$this->id=null;
		unset($_SESSION[$this->context]);
		$this->properties=null;
		if(key_exists(COOKIEVAL,$_COOKIE)){
			unset($_COOKIE[COOKIEVAL]);
		}
	}

  public function login($login,$pwd,$persistent=FALSE){
		$error="";
		$pass=FALSE;
		$belong="";
		$dbdo = DB_DataObject::factory($this->containers[$this->context]['table']);
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
		if(method_exists($dbdo, 'find_by_login')) {
			$result = $dbdo->find_by_login($login);
		} else {
			$dbdo->$lg=$login;
			$result = $dbdo->find(true);
		}

		if(!$result){
			$error=ERROR_NO_USER;
		} else {

			if($callback = $defs['passEncryption']) {
				$pwd = call_user_func($callback,$pwd);
			} else {
			  $pwd = $dbdo->encrypt($pwd);
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
	public function forceLogin($dataobject, $fieldDefinition, $storecnxdate=true)
	{
	 	$this->setId($dataobject->pk());
		$this->currentContainer = $dataobject;
		$this->getPrefs();
		if($fieldDefinition['last_cnx']) {
			$this->setProperty('last_cnx',$this->currentContainer->{$fieldDefinition['last_cnx']});
			$this->currentContainer->{$fieldDefinition['last_cnx']}=date('Y-m-d H:i:s');
			$this->currentContainer->update();
		}
    $this->currentContainer->onSignin();
		$this->store();
	}
	/**
	 * returns the web path user must be redirected to after loging in
	 */
  public function getTarget(){
		return($_SESSION[$this->context]['target']);
	}
  public function setTarget($url){
		$this->target=$_SESSION[$this->context]['target']=$url;
	}
  public function clearTarget() {
		$this->target=$_SESSION[$this->context]['target']=false;
	}

  public function setField($field,$value){
		if($this->isLoggedIn()){
			$this->currentContainer->$field=$value;
			$this->currentContainer->update();
		} else {
			return false;
		}
	}
  public function reloadContainer(){
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
  public function &getDBDO() {
		$this->reloadContainer();
		return $this->currentContainer;
	}
  public function __toString() {
		return $this->currentContainer->__toString();
	}
  public function getField($field){
		if($this->isLoggedIn()){
			return $this->currentContainer->$field;
		} else {
			return false;
		}
	}
}
