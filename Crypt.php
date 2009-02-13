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
* @subpackage   Crypt
*/
/**
* M PHP Framework
*
* Strong encryption/decryption static methods
* Dependency : PEAR_Crypt_Blowfish
*
* @package      M
* @subpackage   Crypt
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Crypt {
public static function encrypt($val,$ky) {
  require_once 'Crypt/Blowfish.php';
  $bf =& Crypt_Blowfish::factory('cbc');
  if (PEAR::isError($bf)) {
            
            echo $bf->getMessage();
            exit;
      }
  $iv = 'abc123+=';

  $bf->setKey($ky, $iv);
  $encrypted = $bf->encrypt($val);
  return base64_encode($encrypted);
}
public static function decrypt( $val, $ky )
   {
        $val = base64_decode($val);
        require_once 'Crypt/Blowfish.php';
        $bf =& Crypt_Blowfish::factory('cbc');
        if (PEAR::isError($bf)) {
                  echo $bf->getMessage();
                  exit;
            }
        $iv = 'abc123+=';
        $bf->setKey($ky, $iv);
        $plaintext = $bf->decrypt($val);
        if (PEAR::isError($plaintext)) {
          echo 'decoding error : ';
          echo $plaintext->getMessage();
        exit;
        }
        return trim($plaintext);
   }
}