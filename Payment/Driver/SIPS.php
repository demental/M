<?php
/**
* M PHP Framework
* @package      M
* @subpackage   Payment_Driver_SIPS
*/
/**
* M PHP Framework
*
* Payment driver for ATOS SIPS
*
* @package      M
* @subpackage   Payment_Driver_SIPS
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Payment_Driver_SIPS extends Payment
{
  protected $_mode = 'cb';
  
  function __construct($options)
  {
    $this->options = $options;
  }
  function fetch() {      
      $amount = $this->order->getAmount()*100;
    	$parm="merchant_id=".$this->getOption('merchant_id');
    	$parm="$parm merchant_country=".$this->getOption('merchant_country');
    	$parm="$parm amount=".$amount;// Valeur exprimée en centimes
    	$parm="$parm currency_code=".$this->getOption('currency_code');//Default = 978 EURO

        //chemin
    	$parm="$parm pathfile=".$this->getOption('path')."/pathfile";

    	//		Si aucun transaction_id n'est affecté, request en génère
    	//		un automatiquement à partir de heure/minutes/secondes
    	//		Référez vous au Guide du Programmeur pour
    	//		les réserves émises sur cette fonctionnalité
    	//

      if(!empty($this->transaction_id)) {
    	    $parm="$parm transaction_id=".$this->transaction_id;
      }
      if(!empty($this->_additionalInfo)) {
        $parm ="$parm return_context=".$this->_additionalInfo;
      }

    	//		Affectation dynamique des autres paramètres
    	// 		Les valeurs proposées ne sont que des exemples
    	// 		Les champs et leur utilisation sont expliqués dans le Dictionnaire des données
    	//
      if($this->getOption('normal_return_url')) {
	 		$parm="$parm normal_return_url=".$this->getOption('normal_return_url');
      }
      if($this->getOption('error_url')) {
	 		$parm="$parm cancel_return_url=".$this->getOption('error_url');
      }
      if($this->getOption('automatic_response_url')) {
	 		$parm="$parm automatic_response_url=".$this->getOption('automatic_response_url');
      }
      if($this->getOption('capture_mode')) {
    			$parm="$parm capture_mode=".$this->getOption('capture_mode');        
      }
      if($this->getOption('capture_day')) {
    			$parm="$parm capture_day=".$this->getOption('capture_day');        
      }
      if($this->getOption('data')) {
    			$parm="$parm data=\"".$this->getOption('data')."\"";        
      }
      if($this->getOption('payment_means')) {
    			$parm="$parm payment_means=\"".$this->getOption('payment_means')."\"";        
      }      
    			$parm="$parm language=".$this->getOption('language');
    	//		$parm="$parm payment_means=CB,2,VISA,2,MASTERCARD,2";
    	//		$parm="$parm header_flag=no";
    	//		$parm="$parm capture_day=";
    	//		$parm="$parm capture_mode=VALIDATION";
    	//		$parm="$parm bgcolor=";
      if($this->getOption('block_align')) {
    			$parm="$parm block_align=".$this->getOption('block_align');
      }
    	//		$parm="$parm block_order=";
    	//		$parm="$parm textcolor=";
    	//		$parm="$parm receipt_complement=";

    			$parm="$parm caddie=".$this->getOrderId();
      if($this->getOption('customer_email')) {
    		$parm="$parm customer_email=".$this->getOption('customer_email');
      }
      if($this->getOption('order_id')) {
    		$parm="$parm order_id=".$this->getOption('order_id');
      }
      if($this->getOption('header_flag')) {
    		$parm="$parm header_flag=".$this->getOption('header_flag');
      }      
    	$parm="$parm customer_ip_address=".$_SERVER['REMOTE_ADDR'];
    	//		$parm="$parm data=";
    	//		$parm="$parm return_context=";
    	//		$parm="$parm target=";
    	//		$parm="$parm order_id=";


    	//		Les valeurs suivantes ne sont utilisables qu'en pré-production
    	//		Elles nécessitent l'installation de vos fichiers sur le serveur de paiement
    	//
    	// 		$parm="$parm normal_return_logo=";
    	// 		$parm="$parm cancel_return_logo=";
    	// 		$parm="$parm submit_logo=";
    	// 		$parm="$parm logo_id=";
    	// 		$parm="$parm logo_id2=";
    	// 		$parm="$parm advert=";
    	// 		$parm="$parm background_id=";
    	// 		$parm="$parm templatefile=";


    	//		insertion de la commande en base de données (optionnel)
    	//		A développer en fonction de votre système d'information

    	// Initialisation du chemin de l'executable request (à modifier)
    	// ex :
    	// -> Windows : $path_bin = "c:\\repertoire\\bin\\request";
    	// -> Unix    : $path_bin = "/home/repertoire/bin/request";
    	//

    	$path_bin = $this->getOption('path')."bin/request";


    	//	Appel du binaire request
      $req=$path_bin.' '.$parm;
    	//	sortie de la fonction : $result=!code!error!buffer!
    	//	    - code=0	: la fonction génère une page html contenue dans la variable buffer
    	//	    - code=-1 	: La fonction retourne un message d'erreur dans la variable error

    	//On separe les differents champs et on les met dans une variable tableau

      $result = exec($req);
    	$tableau = explode ("!", "$result");

    	//	récupération des paramètres

    	$code = $tableau[1];
    	$error = $tableau[2];
    	$message = $tableau[3];
      
    	//  analyse du code retour

      if (( $code == "" ) && ( $error == "" ) )
     	{
      	$output="<br /><center>erreur appel request</center><br />";
      	$output.="executable request non trouve $path_bin<br />Message : <br />";
      	$output.=$message.'<br />'.print_r($result,true).'<br />'.$req;
     	}

    	//	Erreur, affiche le message d'erreur

    	else if ($code != 0){
    		$output="<center><b><h2>Erreur appel API de paiement.</h2></center></b>";
    		$output.="<br><br><br>";
    		$output.=" message erreur : $error <br>";
    	}

    	//	OK, affiche le formulaire HTML
    	else {
//    		$output="<br /><br />";

    		# OK, affichage du mode DEBUG si activé
    		$output.=$error?" $error <br />":'';

    		$output.="  $message <br />";
    	}
      return $output;
  }
  function setResponse($response) {
    $this->transcript = $response;
    $this->fillFromTranscript();
  }
  function getResponse() {
    	$message="message=".$_POST['DATA'];

    	$pathfile="pathfile=".$this->getOption('path')."pathfile";

    	$path_bin = $this->getOption('path')."bin/response";


    	// Appel du binaire response

    	$result=exec("$path_bin $pathfile $message");

    	$tableau = explode ("!", $result);
    	$this->transcript['code'] = $tableau[1];
    	$this->transcript['error'] = $tableau[2];
    	$this->transcript['merchant_id'] = $tableau[3];
    	$this->transcript['merchant_country'] = $tableau[4];
    	$this->transcript['amount'] = $tableau[5];
    	$this->transcript['transaction_id'] = $tableau[6];
    	$this->transcript['payment_means'] = $tableau[7];
    	$this->transcript['transmission_date']= $tableau[8];
    	$this->transcript['payment_time'] = $tableau[9];
    	$this->transcript['payment_date'] = $tableau[10];
    	$this->transcript['response_code'] = $tableau[11];
    	$this->transcript['payment_certificate'] = $tableau[12];
    	$this->transcript['authorisation_id'] = $tableau[13];
    	$this->transcript['currency_code'] = $tableau[14];
    	$this->transcript['card_number'] = $tableau[15];
    	$this->transcript['cvv_flag'] = $tableau[16];
    	$this->transcript['cvv_response_code'] = $tableau[17];
    	$this->transcript['bank_response_code'] = $tableau[18];
    	$this->transcript['complementary_code'] = $tableau[19];
    	$this->transcript['complementary_info']= $tableau[20];
    	$this->transcript['return_context'] = $tableau[21];
    	$this->transcript['caddie'] = $tableau[22];
    	$this->transcript['receipt_complement'] = $tableau[23];
    	$this->transcript['merchant_language'] = $tableau[24];
    	$this->transcript['language'] = $tableau[25];
    	$this->transcript['customer_id'] = $tableau[26];
    	$this->transcript['order_id'] = $tableau[27];
    	$this->transcript['customer_email'] = $tableau[28];
    	$this->transcript['customer_ip_address'] = $tableau[29];
    	$this->transcript['capture_day'] = $tableau[30];
    	$this->transcript['capture_mode'] = $tableau[31];
    	$this->transcript['data'] = $tableau[32];
      $this->fillFromTranscript();

    	//  analyse du code retour

      if (( $code == "" ) && ( $error == "" ) )
     	{
          $response = $result;
          return false;
     	}

    	//	Erreur, sauvegarde le message d'erreur

    	else if ( $code != 0 ){
          $response = $error;
          return;
     	} else {
      }
  }
  function fillFromTranscript() {
    $this->orderId = $this->transcript['caddie'];
    $this->transaction_id=$this->transcript['transaction_id'];
    $this->_additionalInfo = $this->transcript['return_context'];
  }
  public function isSuccess()
  {
    return ($this->transcript['response_code']=='00');
  }
  function getOrderId() {
      return $this->orderId;
  }
  public function setOrder(iOrder $order)
  {
    $this->order = $order;
    $this->orderId = $order->getId();
  }
  public function setAdditionalInfo($info)
  {
    $this->_additionalInfo = base64_encode(serialize($info));
  }
  public function getAdditionalInfo()
  {
    return unserialize(base64_decode($this->_additionalInfo));
  }
  function getTransactionId() {
      return $this->transaction_id;
  }
  public function setTransactionId($id)
  {
    $this->transaction_id=$id;
  }
}