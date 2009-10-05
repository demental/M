<?php
/**
 * M PHP Framework
 *
 * @package      M PHP Framework
 * @subpackage   Calc
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 * @link			http://m4php5.googlecode.com/
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 */

/**
 * Calculation related static methods
 * (Mostly money)
 */

class Calc
{
	/**
	 * Convert Tax free to VAT amount.
	 *
	 * @access	public
	 * @static
	 * @param	float	$price 		Input price
	 * @param	float 	$vat 		VAT Value
	 * @return	Amount				Amount included VAT
	 */
	public static function HT2TTC($price,$vat)
	{
	  if($vat==0) return number_format($price,2,'.','');
   if($price==0) return '0.00';
		$vat=$vat>1?$vat/100:$vat;
   $result = abs($price*(1+$vat));
   $dir = $price/abs($price);
   // We round the result to avoid 1cent difference
   $mod = $result-floor($result);
   if($mod==0) {
     // no round
   } elseif($result < 0.05) {
     $result = 0;
   } elseif(($mod > 0.48 && $mod < 0.50) || ($mod > 0.98) || ($mod > 0.50 && $mod < 0.52) || ($mod < 0.02)) {
     $result=round($result*2)/2;
   }
		return number_format($dir*$result,2, '.', '');
	}
	// Alias
  public static function addVAT($price,$vat)
  {
    return self::HT2TTC($price,$vat);
  }
	/**
	 * Convert VAT amount to Tax free amount.
	 *
	 * @access	public
	 * @static
	 * @param	float	$price 		Input price
	 * @param	float 	$vat 		VAT Value
	 * @return	Amount				Amount excluded VAT
	 */
	public static function TTC2HT($price,$vat)
	{
		$vat=$vat>1?$vat/100:$vat;
		return number_format($price/(1+$vat),2, '.', '');
	}
	// Alias
  public static function removeVAT($price,$vat)
  {
    return self::TTC2HT($price,$vat);
  }
	/**
	 * Return formatted amount with currency.
	 *
	 * @access	public
	 * @static
	 * @param	float	$value 		Input price
	 * @param	string 	$currency	Currency
	 * @return	string	Formatted string
	 */
	public static function money($value,$currency='EUR')
	{
	 return ($value>0?'':'-').money_format('%.2n',abs($value)).$currency;
	}

	/**
	 * Return ratio to calculate prorata amount from start date until month end date.
	 *
	 * @access	public
	 * @static
	 * @param	date	$date 	Start date (eg: 20090301)
	 * @return	float	$ratio	Ratio
	 */
	public static function prorata($date) {

		$nbdays = date('t',strtotime($date));
		$ratio = number_format(1-date('d',strtotime($date))/$nbdays,2);

		return $ratio;
	}
}