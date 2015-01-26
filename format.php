<?php

class Format {

  public static function flatten_digits($phone)
  {
    return preg_replace('`\D`','',$phone);
  }

  public static function phone($phone, $country_code, $display = true)
  {
    $phone = self::flatten_digits($phone);

    if($display) {
      if(strlen($phone)%2==0) {
        $phone = preg_replace('`(\d{2})`','\1 ',$phone);
      } else {
        $phone = preg_replace('`^(\d{2})(\d{1})(\d{2})(\d{2})(\d{2})(\d{2})`','\1 \2 \3 \4 \5 \6',$phone);
      }
    }
    if(strpos($phone,'0')===0) {
      $phone = substr($phone, 1);
      switch($country_code) {
        case 'es':
          $prefix='34';
          break;
        case 'be':
          $prefix='32';
          break;
        default:
          $prefix='33';
          break;
      }
      $phone = '+'.$prefix.($display ? ' ':'').$phone;
    } else {
      $phone = '+'.$phone;
    }
    return $phone;
  }

  public function is_cell_phone($phone)
  {
    if(preg_match('`^0(6|7)`', $phone)) return true;
  }
}
