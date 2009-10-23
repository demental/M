<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Payment
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Credit card payment process abstraction - draft (currently includes a driver for ATOS SIPS)
 *
 */
class Payment {

  protected static $driverpaths = array('M/Payment/Driver/');
  protected $_analysisSummary;
  protected $__name;
	public static final function &factory($driver)
	{
	  $success = false;
		$className = 'Payment_Driver_'.ucfirst($driver);
		$baseName = strtolower($driver).'.php';
    foreach(self::$driverpaths as $path) {
      $classFile = $path.$baseName;
      if(FileUtils::file_exists_incpath($classFile)) {
        $success = true;
        break;
      }
    }
		if($success) {
		  require_once $classFile;
  		$res = new $className();
  		$res->__name = $driver;
  		return $res;
		} else {
		  throw new Exception('Payment :: '.$driver.' driver not found !');
		}
	}
	public final function getName() {
	  return $this->__name;
	}
  public static final function addDriverPath($path)
  {
    if(substr($path,-1,1)!=DIRECTORY_SEPARATOR) {
      $path.=DIRECTORY_SEPARATOR;
    }
    array_unshift(self::$driverpaths,$path);
  }

  /**
   * Creates configuration from a string (serialized data by default)
   */
  public function setConfigFromString($string){
    $config = unserialize($string);
    foreach($config as $k=>$v) {
      $this->setOption($k,$v);
    }

  }

  public function setOption($ky,$val)
  {
    $this->_options[$ky] = $val;
  }
  public function getOption($ky) {
    return $this->_options[$ky];
  }
  /**
   * Retreives the configuration to a string (that can be stored in a database field for example)
   * This string can then be retreived with setConfigFromString
   */
  public function getConfigToString() {
    return serialize($this->_options);
  }

  /**
   * Set a language for payment interface (iso2)
   */
  public function setLanguage($l)
  {
    $this->setOption('language',$l);
  }

  /**
   * Set a currency for payment (3 letters)
   */
  public function setCurrency($c)
  {
    $this->setOption('currency',$c);
  }
  public function setSuccessUrl($url)
  {
    $this->setOption('success_url',$url);
  }
  public function setErrorUrl($url)
  {
    $this->setOption('error_url',$url);
  }
  public function setAutoresponseUrl($url)
  {
    $this->setOption('autoresponse_url',$url);
  }
  
  /**
   * Adds fields to a HTML_QuickForm to configure this driver.
   * Additionnaly A prefix can be prepended to field names
   */
  public function createConfigForm(HTML_QuickForm $form,$prefix=''){
    $this->_configprefix = $prefix;
    foreach($this->_options as $key=>$value) {
      $form->addElement('text',$prefix.$key,$key);
      $defaults[$prefix.$key] = $value;
    }
  }

  /**
   * retreives and creates configuration from exported form values.
   * If a prefix was set previously with configForm(), it's taken in account
   */
  public function processConfigForm($values) {
    foreach($values as $k=>$v) {
      if(!eregi('^'.$this->_configprefix,$k)) continue;
      $this->_options[ereg_replace('^'.$this->_configprefix,'',$k)] = $v;
    }
  }

  /**
   * Attach a transaction to this payment. Tipically a database record.
   * Allows to store result of the transaction
   */
  public final function setTransaction(iTransaction $transaction) {
    $this->_transaction = $transaction;
  }

  public final function getTransaction() {

    return $this->_transaction;
  }
  /**
   * @return bool
   * wether the payment was accepted
   */
  public function isAccepted() {
    return $this->getResponse()->statuscode=='00';
  }

  /**
   * @return bool
   * wether the payment gateway did not give its final response yet
   */
  public function isPending() {
    return $this->getResponse()->statuscode=='pending';    
  }  

  /**
   * Returns a unified status code (at least for accepted) :
   * 00 for payment accepted
   */
  public function getStatusCode() {
    return $this->getResponse()->statuscode;    
  }

  /**
   * Renders and returns an HTML block that must be printed to the enduser
   */
  public function getPublicBlock() {
    return '';
  }

  /**
   * Renders and returns  an HTML block that must be printed to the admin user
   * usage : call centers & check payments
   */
  public function getPrivateBlock() {
    return '';
  }

  /**
   * Sets the response raw data (tipically $_REQUEST) from the payment gateway
   */
  public function parseResponse($response) {
    $this->_rawresponse = $response;
    $_response = new Payment_Response($this);
    $this->_response = $_response;
    return $this;
  }

  /**
   * Gets the response raw data (if previously set in setRawResponse)
   */  
  public final function getRawResponse() {
    return $this->_rawresponse;
  }
  
  public final function getResponse()
  {
    if(!is_a($this->_response,'Payment_Response')) {
      return false;
    }
    return $this->_response;    
  }

  public final function execute()
  {
    if($this->isAccepted()) {
      $this->getTransaction()->success($this);
    } else {
      $this->getTransaction()->error($this);      
    }
  }

  /**
   * Returns a human-readable strings that reflects the current payment mode (example : "credit card")
   * @return string
   */
  public function getMode()
  {
    return;
  }
  /**
   * Returns a human-readable strings that reflects the current payment mode.
   * This method must return a more precise definition
   *  (example : "credit card with thawte analysis")
   * @return string
   */
 public function getDetailedMode()
 {
   return;
 }
 /**
  * Returns the unique identifier of the payment.
  * It can be determined either by the payment gateway or by the local 
  * workflow (e.g. database record id) 
  */
 public function getId()
 {
   return;
 }
// ==========================================================
// = Analysis section (e.g. fia-net is an analysis service)
// = Analysis is provided by third-party companies AFTER payment is being valid.
// ==========================================================

   /**
    * Wether this payment includes an analysis part.
    * @return bool
    */
    public function hasAnalysis()
    {
      return false;
    }
   /**
    * If there is a post-acceptance transaction analysis, isValid can be used for
    * this
    * @return bool
    */
   public function isValid() {
     return $this->isAccepted();
   }

   /**
    * if the payment was accepted, sends a request to the analysis gateway
    * for the transaction.
    * @return bool
    */
   public function requestAnalysis() {
     $this->getTransaction()->tagAsAnalysisSent($this);
   }

   /**
    * Query the analysis gateway to update the validity status, if applicable
    * returns true if analysis was updated since last query, false otherwise.
    * @return bool
    */
   public function updateAnalysis() {
     return;
   }

   /**
    * Returns a link to an analysis detail page, if applicable.
    * @return string (url)
    */
   public function getAnalysisUrl() {
     return;
   }	
	/**
	 * Returns a text summary of the current analysis
	 * @return string
	 */
	 public function getAnalysisSummary()
	 {
	   return $this->_analysisSummary;
	 }


}