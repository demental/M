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

	public static function &factory($driver)
	{
	  $success = false;
		$className = 'Payment_Driver_'.$driver;
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
  		return $res;
		} else {
		  throw new Exception('Payment :: '.$driver.' driver not found !');
		}
	}
  public static function addDriverPath($path)
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
    $this->_config = unserialize($string);
  }

  public function setOption($ky,$val)
  {
    $this->_config[$ky] = $val;
  }
  public function getOption($ky) {
    return $this->_config[$ky];
  }
  /**
   * Retreives the configuration to a string (that can be stored in a database field for example)
   * This string can then be retreived with setConfigFromString
   */
  public function getConfigToString() {
    return serialize($this->_config);
  }

  /**
   * Adds fields to a HTML_QuickForm to configure this driver.
   * Additionnaly A prefix can be prepended to field names
   */
  public function createConfigForm(HTML_QuickForm $form,$prefix=''){
    $this->_configprefix = $prefix;
    foreach($this->_config as $key=>$value) {
      $form->addElement('text',$prefix.$key,$key);
      $defaults[$prefix.$key] = $value;
    }
  }

  /**
   * retreives and creates configuration from exported form values.
   * If a prefix was set previously with configForm(), it's taken in account
   */
  public function processConfigForm($values) {
    $this->_config = $values;
  }

  /**
   * Attach a transaction to this payment. Tipically a database record.
   * Allows to store result of the transaction
   */
  public function setTransaction(iTransaction $transaction) {
    $this->_transaction = $transaction;
  }

  public function getTransaction() {

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
    $_response->statut=$response['statut'];
    $this->_response = $_response;
    return $this;
  }

  /**
   * Gets the response raw data (if previously set in setRawResponse)
   */  
  public function getRawResponse() {
    return $this->_rawresponse;
  }

  public function getResponse()
  {
    if(!is_a($this->_response,'Payment_Response')) {
      return false;
    }
    return $this->_response;    
  }

  public function execute()
  {
    if($this->isAccepted()) {
      $this->getTransaction()->success($this);
    } else {
      $this->getTransaction()->error($this);      
    }
  }
  /**
   * Analysis section (e.g. fia-net is an analysis service)
   * Analysis is provided by third-party companies AFTER payment is being valid.
   */

    /**
     * Wether this payment includes an analysis part.
     */
    public function hasAnalysis()
    {
      return false;
    }
   /**
    * @return bool
    * If there is a post-acceptance transaction analysis, isValid can be used for
    * this
    */
   public function isValid() {
     return;
   }

   /**
    * @return bool
    * if the payment was accepted, sends a request to the analysis gateway
    * for the transaction.
    */
   public function requestAnalysis() {
     $this->getTransaction()->tagAsAnalysisSent();
   }

   /**
    * @return bool
    * Query the analysis gateway to update the validity status, if applicable
    * returns true if analysis was updated since last query, false otherwise.
    */
   public function updateAnalysis() {
     return;
   }

   /**
    * Returns a link to an analysis detail page, if applicable.
    */
   public function getAnalysisUrl() {
     return;
   }	
	
}