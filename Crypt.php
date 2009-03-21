<?php
/**
* M PHP Framework
 * @package      M
 * @subpackage   Crypt
 */
/**
 * M PHP Framework
 *
 *
 * @package      M
 * @subpackage   Crypt
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Strong encryption/decryption static methods
 * Dependency : PEAR_Crypt_Blowfish
 */

class M_Crypt {
	public static function encrypt($val,$ky = null) {
		if(is_null($ky)) {
			$ky = ENCSALT;
		}
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
	public static function decrypt( $val, $ky = null )
	{
		if(is_null($ky)) {
			$ky = ENCSALT;
		}
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