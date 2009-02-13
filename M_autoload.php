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
* @subpackage   M_autoload
*/
/**
* M PHP Framework
*
* Static autoload including paths for most of the framework classes. 
* Declaration of the __autoload() function
* The paths array is stored in the registry object (Mreg).
* You can then add more project-specific classes in the registry at runtime 
*
* @package      M
* @subpackage   M_autoload
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'M/Mreg.php';
Mreg::append('autoload',
array(
  'iSetup'                =>  'M/iSetup.php',
  'DB_DataObject'         =>  'DB/DataObject.php',
  'Cache_Lite'            =>  'Cache/Lite.php',
  'Cache_Lite_File'       =>  'Cache/Lite/File.php',
  'Config'                =>  'M/Config.php',
  'Mtpl'                  =>  'M/Mtpl.php',
  'Calc'                  =>  'M/Calc.php',
  'FileUtils'             =>  'M/FileUtils.php',  
  'SecurityException'     =>  'M/Exception/SecurityException.php',
  'Error404Exception'     =>  'M/Exception/Error404Exception.php', 
  'Module'                =>  'M/Module.php',
  'Component'             =>  'M/Component.php',
  'T'                     =>  'M/T.php',
  'Mail'                     =>  'M/Mail.php',    
  'URL'                   =>  'M/URL.php',
  'db_dataobject_formbuilder_myquickform' => 'DB/DataObject/FormBuilder/MyQuickForm.php',
  'MyQuickForm'           =>  'M/HTML/MyQuickForm.php',
  'MyFB'                  =>  'M/MyFB.php',
  'Log'                   =>  'M/Log.php',
  'User'                  =>  'M/User.php',
  'SmartyLoader'          =>  'classes/SmartyLoader.php',
  'HTML_QuickForm'        =>'HTML/QuickForm.php',
  'Strings'               =>'M/Strings.php',
  'DB_DataObject_Iterator'=>'M/DB/DataObject/Iterator.php',
  'DB_DataObject_Pluggable'=>'M/DB/DataObject/Pluggable.php',
  'DB_DataObject_FormBuilder'=>'DB/DataObject/FormBuilder.php',
  'HTML_QuickForm_Renderer_ArraySmarty'=>'HTML/QuickForm/Renderer/ArraySmarty.php',
  'HTML_QuickForm_Renderer_Array'=>'HTML/QuickForm/Renderer/Array.php',
  'Calendrier'            => 'classes/Calendrier.php',
  'DayIterator'           => 'classes/Calendrier.php',
  'Mreg'                  => 'M/Mreg.php',
  'iListener'             =>'M/iListener.php',
  'Dispatcher'            =>'M/Dispatcher.php',
  'Module_CMS'            =>'M/Module/CMS.php',
  'Maman'                 =>'M/Maman.php',
  'M_Office_Util'         =>'M/Office/Util.php',
  'AuthHelper'            =>'M/Office/AuthHelper.php',
  'MArray'                =>'M/MArray.php',
  'Office_DefaultModule'   =>'M/Office/DefaultModule.php',
  'Office_UserModule'     =>'M/Office/UserModule.php',
  'Office_DbModule'       =>'M/Office/DbModule.php',
  'Payment'               =>'M/Payment.php',
  'iOrder'                =>'M/Payment/iOrder.php',
  'phpmailer'             =>'M/lib/phpmailer/class.phpmailer.php',
  'M_Crypt'               =>'M/Crypt.php',
  'MPdf'                  =>'M/MPdf.php',
  'TCPDF'                 =>APP_ROOT.WEB_FOLDER.'tcpdf/tcpdf.php',
  'MGeo'                  =>'M/MGeo.php',
  'Net_URL_Mapper'        =>'Net/URL/Mapper.php'
  )
);

function __autoload($class) {
  $classes = Mreg::get('autoload');
  if(key_exists($class,$classes)) {
    require $classes[$class];
    return true;
  }
  try {
    $callbacks = Mreg::get('autoloadcallback');
    if(is_array($callbacks)) {
      foreach($callbacks as $callback) {
        if(function_exists($callback)) {
          call_user_func($callback,$class);
        }
      }
    }
  } catch (Exception $e) {
    
  }
  return false;
}

