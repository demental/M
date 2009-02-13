<?php
//
// +--------------------------------------------------------------------+
// | M PHP Framework                                                    |
// +--------------------------------------------------------------------+
// | Copyright (c) 2003-2009 Arnaud Sellenet demental.info              |
// | Web           http://m4php5.googlecode.com/                        |
// | License       GNU Lesser General Public License (LGPL)             |
// +--------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or      |
// | modify it under the terms of the GNU Lesser General Public         |
// | License as published by the Free Software Foundation; either       |
// | version 2.1 of the License, or (at your option) any later version. |
// +--------------------------------------------------------------------+
//

/**
* M PHP Framework
* @package      M
* @subpackage   Calc
*/
/**
* M PHP Framework
*
* Calculation related static methods
* Mostly money
*
* @package      M
* @subpackage   Calc
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Calc
{
	public static function HT2TTC($prix,$tva)
	{
		$tva=$tva>1?$tva/100:$tva;
		return number_format($prix*(1+$tva),2, '.', '');
	}
	public static function TTC2HT($prix,$tva)
	{
		$tva=$tva>1?$tva/100:$tva;
		return number_format($prix/(1+$tva),2, '.', '');
	}
	public static function money($value,$currency='EUR')
	{
	 return ($value>0?'':'-').money_format('%.2n',abs($value)).$currency;
	}
	// ============================
	// = This one is date-related =
	// ============================
	public static function prorata($date) {

    $nbdays = date('t',strtotime($date));
    $ratio = number_format(1-date('d',strtotime($date))/$nbdays,2);
    
    return $ratio;
	}
}