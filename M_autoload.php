<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   M_autoload
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 * Static autoload including paths for most of the framework classes.
 * Declaration of the __autoload() function
 * The paths array is stored in the registry object (Mreg).
 * You can then add more project-specific classes in the registry at runtime
 */

require_once 'M/Mreg.php';
require 'M/M.php';
Mreg::append('autoload',
array(
  'isetup'                =>  'M/iSetup.php',
  'db_dataobject'         =>  'DB/DataObject.php',
  'cache_lite'            =>  'Cache/Lite.php',
  'cache_lite_file'       =>  'Cache/Lite/File.php',
  'config'                =>  'M/Config.php',
  'mtpl'                  =>  'M/Mtpl.php',
  'calc'                  =>  'M/Calc.php',
  'fileutils'             =>  'M/FileUtils.php',  
  'securityexception'     =>  'M/Exception/SecurityException.php',
  'error404exception'     =>  'M/Exception/Error404Exception.php', 
  'module'                =>  'M/Module.php',
  'component'             =>  'M/Component.php',
  't'                     =>  'M/T.php',
  'mail'                     =>  'M/Mail.php',    
  'url'                   =>  'M/URL.php',
  'db_dataobject_formbuilder_myquickform' => 'DB/DataObject/FormBuilder/MyQuickForm.php',
  'myquickform'           =>  'M/HTML/MyQuickForm.php',
  'myfb'                  =>  'M/MyFB.php',
  'log'                   =>  'M/Log.php',
  'user'                  =>  'M/User.php',
  'html_quickform'        =>'HTML/QuickForm.php',
  'strings'               =>'M/Strings.php',
  'db_dataobject_pluggable'=>'M/DB/DataObject/Pluggable.php',
  'db_dataobject_formbuilder'=>'DB/DataObject/FormBuilder.php',
  'html_quickform_renderer_arraysmarty'=>'HTML/QuickForm/Renderer/ArraySmarty.php',
  'html_quickform_renderer_array'=>'HTML/QuickForm/Renderer/Array.php',
  'calendrier'            => 'classes/Calendrier.php',
  'dayiterator'           => 'classes/Calendrier.php',
  'mreg'                  => 'M/Mreg.php',
  'ilistener'             =>'M/iListener.php',
  'dispatcher'            =>'M/Dispatcher.php',
  'cms_module'            =>'M/Module/CMS.php',
  'payment_module'        =>'M/Module/Payment.php',
  'payment_response'      =>'M/Payment/Response.php',
  'maman'                 =>'M/Maman.php',
  'm_office_util'         =>'M/Office/Util.php',
  'authhelper'            =>'M/Office/AuthHelper.php',
  'marray'                =>'M/MArray.php',
  'office_defaultmodule'   =>'M/Office/DefaultModule.php',
  'office_usermodule'     =>'M/Office/UserModule.php',
  'office_dbmodule'       =>'M/Office/DbModule.php',
  'payment'               =>'M/Payment.php',
  'itransaction'          =>'M/Payment/iTransaction.php',
  'Payment_Request_Exception'=>'M/Payment/Exception.php',
  'Payment_Response_Exception'=>'M/Payment/Exception.php',  
  'phpmailer'             =>'M/lib/phpmailer.php',
  'm_crypt'               =>'M/Crypt.php',
  'mpdf'                  =>'M/MPdf.php',
  'mpdf_form'             =>'M/MPdf/form.php',
  'tcpdf'                 =>APP_ROOT.WEB_FOLDER.'tcpdf/tcpdf.php',
  'mgeo'                  =>'M/MGeo.php',
  'net_url_mapper'        =>'Net/URL/Mapper.php',
  'iquerystorable'        =>'M/Plugin/Exporter/iQueryStorable.php',
  'pluginregistry'        =>'M/PluginRegistry.php',
  'm_plugin'              =>'M/Plugin.php',
  'icommand'              =>'M/iCommand.php',
  'ianalyzabletransaction'=>'M/Payment/iAnalyzable.php',
  'ianalyzableorder'      =>'M/Payment/iAnalyzable.php',
  'ianalyzableorderline'      =>'M/Payment/iAnalyzable.php',
  'ianalyzablecustomer'   =>'M/Payment/iAnalyzable.php',
  'ianalyzableaddress'    =>'M/Payment/iAnalyzable.php',
  'ianalyzableshippingmethod'    =>'M/Payment/iAnalyzable.php',
  'payment_response_exception'=>'M/Payment/Exception.php',
  'payment_request_exception'=>'M/Payment/Exception.php'
  )
  );

  /**
   *
   * Autoload
   *
   * @param	$class	string	Class to load
   * @return	boolean
   */
  function __autoload($class) {
  	$classes = Mreg::get('autoload');
    // Switch to lowercase as PHP is not case sensitive for objects and methods while it is for array keys
    $class = strtolower($class);
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


