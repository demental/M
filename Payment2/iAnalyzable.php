<?php
interface iAnalyzableTransaction {

  /**
   * Returns the order object attached to $this
   * @return iAnalyzableOrder
   */
  public function getOrder();


  /**
   * returns the payment date for $this
   * @return date('Y-m-d H:i:s')
   */
  public function getPaymentDate();


  /**
   * returns the customer IP when he paid $this
   * @return string (IP address formatted)
   */
  public function getCustomerIp();


  /**
   * returns the customer object attached to $this
   * @return iAnalyzableCustomer
   */
  public function getCustomer();


  /**
   * returns a unique identifier that will be the reference of this transaction
   * for the analysis system.
   * Most of the time this may be the order reference number.
   * @return string
   */
  public function getAnalysisReference();
  
  /**
   * Method triggered first time the analysis is requested for it
   */
  public function tagAsAnalysisSent(Payment $p);
  
}

interface iAnalyzableOrder {

  /**
   * returns the lines that will be sent for analysis (tipically article lines)
   * @return array(iAnalyzableOrderline,iAnalyzableOrderline,...)
   */
  public function getAnalyzableLines();


  /**
   * returns the billing address object
   * @return iAnalyzableAddress
   */
  public function getBillingAddress();


  /**
   * returns the billing address object
   * @return iAnalyzableAddress
   */
  public function getShippingAddress();


  /**
   * returns the total amount of the order
   * @return float
   */
  public function getAmount();


  /**
   * returns the total number of items included in the order's shopping cart
   * @return float
   */
  public function countAnalyzableItems();


  /**
   * wether or not this order was paid using several transactions
   * @return bool
   */
  public function hasMultiPayments();


  /**
   * returns the shipping method attached to $this
   * @return iAnalyzableShippingMethod
   */ 
  public function getShippingMethod();
}

interface iAnalyzableCustomer {
  /**
   * Wether the customer is a professional or an individual
   * @return bool
   */
  public function isBusiness();
  /**
   * Following methods return string information
   * about the customer's identity
   * the method names are self-explanatory
   * @return string
   */
  public function getSalutation();
  public function getFirstName();
  public function getLastName();
  public function getCompanyName();
  public function getLandlinePhone();
  public function getMobilePhone();
  public function getEmailAddress();
}

interface iAnalyzableAddress {

  /**
   * @return string
   */
  public function getAddress1();


  /**
   * @return string
   */
  public function getAddress2();


  /**
   * @return string
   */
  public function getZipcode();

  /**
   * @return string
   */
  public function getCityname();

  /**
   * returns the ISO2 code for the country of $this
   * @return string(2)
   */
  public function getCountrycode();

  /**
   * returns the type of address (must return 'billing' or 'shipping')
   * @return string
   */
  public function getAddressType();
}

interface iAnalyzableOrderline {

  /**
   * returns the listing product reference for this item
   * @return string
   */
  public function getReference();


  /**
   * returns the unit sale price for this item
   * @return float
   */
  public function getPricePerUnit();


  /**
   * returns the quantity present in this line
   * @return float
   */
  public function getQtty();


  /**
   * returns the item category code.
   * The category code must comply to the one proposed by fia-net :
   * 1 : Alimentation & gastronomie 
   * 2 : Auto & moto 
   * 3 : Culture & divertissements 
   * 4 : Maison & jardin 
   * 5 : Electroménager 
   * 6 : Enchères et achats groupés 
   * 7 : Fleurs & cadeaux 
   * 8 : Informatique & logiciels 
   * 9 : Santé & beauté 
   * 10 : Services aux particuliers 
   * 11 : Services aux professionnels 
   * 12 : Sport 
   * 13 : Vêtements & accessoires 
   * 14 : Voyage & tourisme 
   * 15 : Hifi, photo & vidéos 
   * 16 : Téléphonie & communication 
   * 
   * 
   * @return int
   */
  public function getItemCategoryCode();


  /**
   * returns a short item description
   * @return string
   **/ 
  public function getItemDescription();
}

interface iAnalyzableShippingMethod {

  /**
   * Returns the shipping speed code.
   * Must comply to the ones proposed by fia-net :
   * 1 : Express (- de 24 heures)
   * 2 : Standard
   */
  public function getSpeedCode();


  /**
   * Returns the shipping service company name
   * @return string
   */
  public function getCompanyName();
  
  /**
   * Returns the shipping type
   * Must comply to the ones proposed by fia-net :
   * 1 : Retrait de la marchandise chez le marchand 
   * 2 : Utilisation d'un réseau de points-retrait tiers (type kiala, alveol, etc.)  
   * 3 : Retrait dans un aéroport, une gare ou une agence de voyage 
   * 4 : Transporteur (La Poste, Colissimo, UPS, DHL... ou tout transporteur privé) 
   * 5 : Emission d’un billet électronique, téléchargements
   */
  public function getShippingType();
}