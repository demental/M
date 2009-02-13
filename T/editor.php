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
* @subpackage   T_editor
*/
/**
* M PHP Framework
*
* Translation driver for editing purpose :
* - if a translation request is not present this driver adds it to the translations array
* - stores all used transation strings requested during the script
* 
* @package      M
* @subpackage   T_editor
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class T_editor extends T {
  public static $curStrings;
  public function translate( $string, $args = null )
  {
    if(empty($string)) {
      return;
    }
    self::$curStrings[]=$string;
      if(!key_exists($string,$this->strings)) {
        return vsprintf($string,$args);
      }
      $string = $this->strings[$string];
      if(is_array($args)) {
        return vsprintf($string,$args);
      }
      return $string;
  }
  public static function getCurStrings()
  {
    return array_unique(self::$curStrings);
  }
}