<?php
/**
  * Calculation related static methods
  * Mostly money
**/
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