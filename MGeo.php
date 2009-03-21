<?php
/**
* M PHP Framework
* @package      M
* @subpackage   MGeo
*/
/**
* M PHP Framework
*
* Helper class to work with google maps geocoding API
* Also provides helper methods for satellite settings (azimut - elevation - polarity)
*
* @package      M
* @subpackage   MGeo
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

define('PI',3.1415926536);
class MGeo {
    public $address;
    public $key;
    public function __construct($key = null) {
      if(is_null($key)) {
        $this->key = Config::getPref('gmap_key');
      } else {
        $this->key = $key;
      }
    }
    public function setAddress($address)
    {
      foreach(array('street','city','zipcode','country') as $k) {
        if(!key_exists($k,$address)) {
          throw new Exception('Bad address format for Geo object');
        }
      }
      $this->address = $address;
    }
    public function setDebug($bool)
    {
      $this->_debug=$bool;
    }
    public function debug($str)
    {
      echo $str.'<br />';
    }
    public function setString($str)
    {
      $this->q = $str;
    }
    public function setLatLong($lat,$long)
    {
      $this->lat = $lat;
      $this->long = $long;
    }
    public function getLatLong()
    {
      if(empty($this->q)) {
        $this->q = $this->address['street'].', '.$this->address['city'].', '.$this->address['zipcode'].', '.$this->address['country']; 
      }
      if($this->_debug) {
        $this->debug('fetching : '.$this->q);
      }
      $url = "http://maps.google.com/maps/geo?q=".urlencode($this->q)."&output=csv&key=".$this->key;
      if($this->_debug) {
        $this->debug('url : '.$url);
      }
      $coords = file_get_contents($url);
      if($this->_debug) {
        $this->debug('result : '.$coords);
      }
      list($code,$accuracy,$lat,$long) = explode(',',$coords);
      if($code==602) {
        $this->q = $this->address['city'].', '.$this->address['zipcode'].', '.$this->address['country'];
        $url = "http://maps.google.com/maps/geo?q=".urlencode($this->q)."&output=csv&key=".$this->key;
        $coords = file_get_contents($url);
        list($code,$accuracy,$lat,$long) = explode(',',$coords);
      }
      $this->lat = $lat;
      $this->long = $long;
      return array($lat,$long);
    }
    // Azimut Elevation Polarite
    // @param $satlong float ° du sat
    public function getAEP($satlong = 13)
    {
      $rayon=6378000;
      $sat_alt=35786000;

      $this->azimut = $this->calculAzimut($satlong,$this->lat,$this->long);//180+180*atan(-1*tan(PI*($po-$long)/180)/sin(PI*$lat/180))/PI;
      $el=acos(cos(PI/180*($this->long-$satlong))*cos(PI/180*abs($this->lat)));
      $this->elevation=180*atan((cos($el)-($rayon/($rayon+$sat_alt)))/sin($el))/PI;

      $longdiffr=(13-$this->long)/57.29578;
      $eslatr=$this->lat/57.29578;
      $tilt=-57.29578*atan(sin($longdiffr)/tan($eslatr));
      $this->polarite=$tilt;
      return array($this->azimut,$this->elevation,$this->polarite);
      
    }
  private function calculAzimut($satlong,$eslat,$eslong)
  {
    $longdiffr=($eslong-$satlong)/57.29578;
    $esazimuth=180+57.29578*atan(tan($longdiffr)/sin(($eslat/57.29578)));
    if ($eslat<0) $esazimuth=$esazimuth-180; 
    if ($esazimuth<0) $esazimuth=$esazimuth+360.0;
    //magnetic field model
    //table below updated 17 Apr 2006, centered on 17 April 2007, needs updating in Apr 2008 to keep errors under 0.2 deg
    $dev[0]=4.58;     $dev[1]=7.21;     $dev[2]=9.58;     $dev[3]=16.48;    $dev[4]=46.03;
    $dev[5]=19.52;    $dev[6]=12.2;     $dev[7]=9.28;     $dev[8]=17.25;    $dev[9]=42.62;
    $dev[10]=21.9;    $dev[11]=12.42;   $dev[12]=9.13;    $dev[13]=16.83;   $dev[14]=38.92;
    $dev[15]=-8.28;   $dev[16]=0.33;    $dev[17]=4.61;    $dev[18]=13.92;   $dev[19]=28.95;
    $dev[20]=-28.45;  $dev[21]=-16.08;  $dev[22]=-14.6;   $dev[23]=-8.15;   $dev[24]=10.55;
    $dev[25]=-18.72;  $dev[26]=-11.45;  $dev[27]=-18.7;   $dev[28]=-24.13;  $dev[29]=-5.7;
    $dev[30]=-3.5;    $dev[31]=-0.98;   $dev[32]=-6.43;   $dev[33]=-23.5;   $dev[34]=-19.32;
    $dev[35]=9.12;    $dev[36]=3.32;    $dev[37]=0.63;    $dev[38]=-23.92;  $dev[39]=-39.95;
    $dev[40]=16.42;   $dev[41]=2.18;    $dev[42]=-4.15;   $dev[43]=-32.12;  $dev[44]=-60.65;
    $dev[45]=7.9;     $dev[46]=0.02;    $dev[47]=-2.48;   $dev[48]=-18.33;  $dev[49]=-73.8;
    $dev[50]=-12.8;   $dev[51]=-4.8;    $dev[52]=1.08;    $dev[53]=-0.77;   $dev[54]=-62.3;
    $dev[55]=-11.35;  $dev[56]=-2.65;   $dev[57]=5.5;     $dev[58]=11;      $dev[59]=39.35;
    $dev[60]=4.58;    $dev[61]=7.21;    $dev[62]=9.58;    $dev[63]=16.48;   $dev[64]=46.03;

    $latit=1.0*$eslat;
    $longi=1.0*$eslong;
    if ($latit==60.0) $latit=59.99999;
    if ($latit >59.999999) return $esazimuth+1000;
    if ($latit <-60) return $esazimuth+1000;
    if ($longi==180.0) $longi=179.99999;
    if ($longi>179.999999) $longi=$longi-360;
    if ($longi<-180) $longi=$longi+360;
    $a = round(((1.0*$longi + 180)/30)-0.5);
    $b = 3- round(((1.0*$latit + 60)/30)-0.5);
    $c=$a * 5 + $b;
    //calculate left proportion up to 30.
    if ($latit>=30) $pl=$latit-30;
    else
    if($latit>=0) $pl=$latit;
    else
    if ($latit>=-30) $pl=30+$latit;
    else
    $pl = 60+$latit;
    //calculate hoziz proportion up to 30.
    $pr=$longi +180 - $a*30;
    //return pr;   ok to here
    $u1=$dev[$c+1]+($dev[$c]-$dev[$c+1])*$pl/30;
    $u2=$dev[$c+6]+($dev[$c+5]-$dev[$c+6])*$pl/30;
    $um=$u1+($u2-$u1)*$pr/30;
    //return um;
    $esazimuthm=$esazimuth-$um;
    if ($esazimuthm<-180) $esazimuthm=$esazimuthm+360; 
    if ($esazimuthm>360) $esazimuthm=$esazimuthm-360.0;
    return $esazimuthm;  
  }
}