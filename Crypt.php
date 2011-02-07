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
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Strong encryption/decryption static methods
 * Dependency : PEAR_Crypt_Blowfish
 */

class M_Crypt
{
	
	/**
	 * Encrypt data
	 *
	 * @access	public
	 * @static
	 * @param	string	$val	Data to encrypt
	 * @param	string 	$ky		Key
	 * @return	string	Encrypted data
	 */
	public static function encrypt($val,$ky = null)
	{
		if(is_null($ky)) {
			$ky = ENCSALT;
		}
    if(empty($val)) return '';
		require_once 'Crypt/Blowfish.php';
		$bf =& Crypt_Blowfish::factory('cbc');
		if (PEAR::isError($bf)) {
			die($bf->getMessage());
		}
		$iv = 'abc123+=';

		$bf->setKey($ky, $iv);
		$encrypted = $bf->encrypt($val);
		return base64_encode($encrypted);
	}

	/**
	 * Encrypt data
	 *
	 * @access	public
	 * @static
	 * @param	string	$val	Data to encrypt
	 * @param	string 	$ky		Key
	 * @return	string	Encrypted data
	 */
	public static function decrypt( $val, $ky = null )
	{
		if(is_null($ky)) {
			$ky = ENCSALT;
		}
    if(empty($val)) return '';		
		$val = base64_decode($val);
		require_once 'Crypt/Blowfish.php';
		$bf =& Crypt_Blowfish::factory('cbc');
		if (PEAR::isError($bf)) {
			die($bf->getMessage());
		}
		$iv = 'abc123+=';
		$bf->setKey($ky, $iv);
		$plaintext = $bf->decrypt($val);
		if (PEAR::isError($plaintext)) {
			die('decoding error : '.$plaintext->getMessage());
		}
		return trim($plaintext);
	}
}