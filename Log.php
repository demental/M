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
* @subpackage   Log
*/
/**
* M PHP Framework
*
* Very basic log class
*
* @package      M
* @subpackage   Log
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class Log {
  
  const LOGLEVEL_INFO = 'Info';
  const LOGLEVEL_WARNING = 'Warning';
  const LOGLEVEL_NOTICE = 'Notice';
  
  public static function info ($message)
  {
    self::message($message);
  }
  
  public static function message($message,$level = Log::LOGLEVEL_INFO) {

      return;
    $file = Config::get('logfile');
    file_put_contents($file,date('Y-m-d H:i:s').' == '.$level.' == '.$message."\n",FILE_APPEND);
    
  }
  public static function mail($message) {
        $m = Mail::factory('vide');
        $m->setVars(array('subject'=>'Rapport log sur '.SITE_URL,'body'=>$message.'<h3>Requête :</h3><pre>'.print_r($_REQUEST,true).'</pre><h3>Session</h3><pre>'.print_r($_SESSION,true)));
        $m->sendTo(TECH_EMAIL);
  }
}
?>
