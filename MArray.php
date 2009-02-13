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
* @subpackage   MArray
*/
/**
* M PHP Framework
*
* Static class providing array related helper methods
*
* @package      M
* @subpackage   MArray
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class MArray {
  public static function array_merge_recursive_unique( $first, $second, $greedy=false) {
     $inter = array_intersect_assoc(array_keys($first), array_keys($second)); # shaired keys
  # the idea next, is to strip and append from $second into $first
     foreach ( $inter as $key ) {
     # recursion if both are arrays
       if ( is_array($first[$key]) && is_array($second[$key]) ) {
           $first[$key] = self::array_merge_recursive_unique($first[$key], $second[$key]);
       }
     # non-greedy array merging:
       else if ( is_array($first[$key] && !$greedy ) ) {
           $first[$key][] = $second[$key];
       }
       else if ( is_array($second[$key]) && !$greedy ) {
           $second[$key][] = $first[$key];
           $first[$key] = $second[$key];
       }
     # overwrite...
       else {
           $first[$key] = $second[$key];
       }
       unset($second[$key]);
     }
  # merge the unmatching keys onto first
     return array_merge($first, $second);
  }
  
}