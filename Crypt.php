<?php
// ===============================================
// = Strong encryption/decryption static methods
// = Dependency : PEAR_Crypt_Blowfish
// ===============================================
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