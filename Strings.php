<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Strings
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Various string manipulation methods
 *
 */
class Strings {
	public static function stripify($string,$moduleCompliant=false) {
		$string = trim($string);
		if($moduleCompliant) {

			$string=strtr(utf8_decode($string),utf8_decode('+ÂÔÊÎÛàâéêèîôûùç² ,!?&\'"./'),'_aoeiuaaeeeiouuc2_________');
			$sep='_';
		} else {
			$string=strtr(utf8_decode($string),utf8_decode('ÂÔÊÎÛàâéêèîôûùç² ,!?&\'"./'),'aoeiuaaeeeiouuc2---------');
			$sep='_';
		}

		$string=ereg_replace('('.$sep.'+)',$sep,$string);
		$string=ereg_replace(''.$sep.'$','',$string);
		$string = str_replace('?','',$string);
		$string = str_replace('_-','_',$string);
		$string = str_replace('-_','-',$string);

		$string = str_replace('--','-',$string);
		$string = str_replace('__','_',$string);
		$string=preg_replace('`\s+`','_',$string);

		return strtolower($string);
	}
	public function unspacify($string)
	{
		$string = trim($string);
		$string=preg_replace('`\s+`','_',$string);
		$string = str_replace('_-','_',$string);
		$string = str_replace('-_','-',$string);
		$string = ereg_replace('--','-',$string);
		$string=preg_replace('`(_|-)+`','_',$string);
		$string=str_replace('&','and',$string);
		return $string;
	}
	public static function stripLines($text)
	{
		$text = str_replace(CHR(10),"",$text);
		// et celle là aussi : 
		$text = str_replace(CHR(13),"",$text);
		return $text;
	}
	public static function cleanFileName($name)
	{
		eregi('(.*)\.([^\.]+)$',$name,$tab);
		return self::stripify($tab[1]).'.'.$tab[2];
	}
	public static function stripifycity($city)
	{
		$city=self::stripify($city);
		$city=str_replace('saint-','st-',$city);
		$city = str_replace('-sur-','-s-',$city);
		return $city;
	}
	public static function number_unformat ( $string )
	{
		$string=preg_replace('`[^0-9]`i','',$string);
		return $string;
	}
	public static function extractEmails ($string)
	{
		preg_match_all('`([[:alnum:]]([-_.]?[[:alnum:]])*@[[:alnum:]]([-.]?[[:alnum:]])*\.([a-z]{2,4}))`i',$string,$res);
		return $res[1];
	}
	public static function truncate ($string,$length,$end=' ...')
	{
		$res = preg_replace('`^(.{'.$length.'}[^ ]*) .*$`s','$1'.$end,$string);
		return $res;
	}
	public static function ucsentence ($string){
		$string = explode ('.', $string);
		$count = count ($string);
		for ($i = 0; $i < $count; $i++){
			$string[$i]  = ucfirst (strtolower(trim ($string[$i])));

			if ($i > 0){
				if ((ord($string[$i]{0})<48) || (ord($string[$i]{0})>57)) {
					$string[$i] = ' ' . $string[$i];
				}
			}
		}
		$string = implode ('.', $string);
		return $string;
	}
	public static function create_guid()
	{
		$microTime = microtime();
		list($a_dec, $a_sec) = explode(" ", $microTime);

		$dec_hex = sprintf("%x", $a_dec* 1000000);
		$sec_hex = sprintf("%x", $a_sec);

		ensure_length($dec_hex, 5);
		ensure_length($sec_hex, 6);

		$guid = "";
		$guid .= $dec_hex;
		$guid .= create_guid_section(3);
		$guid .= '-';
		$guid .= create_guid_section(4);
		$guid .= '-';
		$guid .= create_guid_section(4);
		$guid .= '-';
		$guid .= create_guid_section(4);
		$guid .= '-';
		$guid .= $sec_hex;
		$guid .= create_guid_section(6);

		return $guid;

	}
	/*    public static function unaccent($str)
	 {
	 $str=strtr(utf8_decode(strtolower($str)),utf8_decode('ûùüàâéêèëîïöôç'),'uuuaaeeeeiiooc');      
	 return $str;
	 }*/
	public static function unaccent($str, $charset='utf-8',$removePunct=true)
	{
		$str = htmlentities($str, ENT_NOQUOTES, $charset);

		$str = preg_replace('#\&([A-za-z])(?:uml|circ|tilde|acute|grave|cedil|ring)\;#', '\1', $str);
		$str = preg_replace('#\&([A-za-z]{2})(?:lig)\;#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		if($removePunct) {
			$str = preg_replace('#\&[^;]+\;#', '', $str); // supprime les autres caractères
			$str = preg_replace('#\W|\s#',' ',$str);
		}
		return trim($str);
	}

	public static function autocap($text)
	{
	  while(ereg('[A-Z]{2}',$text)) {
	    $text = preg_replace('/([A-Z][A-Z]+)/e',"strtolower('\\1')",$text);
	  }
    $text = preg_replace('/\. ?([a-z])/e',"'. '.strtoupper('\\1')",$text);
		return ucfirst($text);
	}
	public static function aucfirst(&$item,$key)
	{
		$item = ucfirst(trim(strtolower($item)));
	}


}
function create_guid_section($characters)
{
	$return = "";
	for($i=0; $i<$characters; $i++)
	{
		$return .= sprintf("%x", mt_rand(0,15));
	}
	return $return;
}

function ensure_length(&$string, $length)
{
	$strlen = strlen($string);
	if($strlen < $length)
	{
		$string = str_pad($string,$length,"0");
	}
	else if($strlen > $length)
	{
		$string = substr($string, 0, $length);
	}
}

function microtime_diff($a, $b) {
	list($a_dec, $a_sec) = explode(" ", $a);
	list($b_dec, $b_sec) = explode(" ", $b);
	return $b_sec - $a_sec + $b_dec - $a_dec;
}