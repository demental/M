<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   M_autoload
 * @author       Arnaud Sellenet <demental@sat2way.com>
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
  'mhook'                =>  'M/MHook.php',
  'db_dataobject'         =>  'DB/DataObject.php',
  'cache_lite'            =>  'Cache/Lite.php',
  'command'               =>  'M/Command.php',
  'notifier'              =>  'M/Notifier.php',
  'cache_lite_file'       =>  'Cache/Lite/File.php',
  'config'                =>  'M/Config.php',
  'mtpl'                  =>  'M/Mtpl.php',
  'mtpl_filter'           =>  'M/Mtpl/filter.php',
  'mtpl_assetblock'       =>  'M/Mtpl/assetblock.php',
  'calc'                  =>  'M/Calc.php',
  'fileutils'             =>  'M/FileUtils.php',
  'securityexception'     =>  'M/Exception/SecurityException.php',
  'error404exception'     =>  'M/Exception/Error404Exception.php',
  'module'                =>  'M/Module.php',
  'component'             =>  'M/Component.php',
  't'                     =>  'M/T.php',
  'dao'                   =>  'M/DAO.php',
  'mail'                  =>  'M/Mail.php',
  'url'                   =>  'M/URL.php',
	'faker'									=>  'M/lib/faker/faker.php',
  'db_dataobject_formbuilder_myquickform' => 'DB/DataObject/FormBuilder/MyQuickForm.php',
  'myquickform'           =>  'M/HTML/MyQuickForm.php',
  'myfb'                  =>  'M/MyFB.php',
  'log'                   =>  'M/Log.php',
  'user'                  =>  'M/User.php',
  'mdb2'                  =>  'MDB2.php',
  'html_quickform'        =>'HTML/QuickForm.php',
  'strings'               =>'M/Strings.php',
  'db_dataobject_pluggable'=>'M/DB/DataObject/Pluggable.php',
  'db_migration'          => 'M/DB/Migration.php',
  'db_dataobject_formbuilder'=>'DB/DataObject/FormBuilder.php',
  'html_quickform_renderer_arraysmarty'=>'HTML/QuickForm/Renderer/ArraySmarty.php',
  'html_quickform_renderer_array'=>'HTML/QuickForm/Renderer/Array.php',
  'calendrier'            => 'classes/Calendrier.php',
  'dayiterator'           => 'classes/Calendrier.php',
  'mreg'                  => 'M/Mreg.php',
  'ilistener'             =>'M/iListener.php',
  'dispatcher'            =>'M/Dispatcher.php',
  'cms_module'            =>'M/Module/CMS.php',
  'module_cms'            =>'M/Module/CMS_old.php',
  'payment_module'        =>'M/Module/Payment.php',
  'payment_response'      =>'M/Payment/Response.php',
  'maman'                 =>'M/Maman.php',
  'm_office_util'         =>'M/Office/Util.php',
  'm_office_search'       =>'M/Office/Search.php',
  'authhelper'            =>'M/Office/AuthHelper.php',
  'marray'                =>'M/MArray.php',
  'office_defaultmodule'  =>'M/Office/DefaultModule.php',
  'office_usermodule'     =>'M/Office/UserModule.php',
  'office_dbmodule'       =>'M/Office/DbModule.php',
  'payment'               =>'M/Payment.php',
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
  'iorder'                =>'M/Payment/iOrder.php',
  'itransaction'          =>'M/Payment2/iTransaction.php',
  'ianalyzabletransaction'=>'M/Payment2/iAnalyzable.php',
  'ianalyzableorder'      =>'M/Payment2/iAnalyzable.php',
  'ianalyzableorderline'      =>'M/Payment2/iAnalyzable.php',
  'ianalyzablecustomer'   =>'M/Payment2/iAnalyzable.php',
  'ianalyzableaddress'    =>'M/Payment2/iAnalyzable.php',
  'ianalyzableshippingmethod'    =>'M/Payment2/iAnalyzable.php',
  'imaildriver'    =>'M/Mail/iMailDriver.php',
  'payment_response_exception'=>'M/Payment2/Exception.php',
  'payment_request_exception'=>'M/Payment2/Exception.php',
  'notfoundexception'=>'M/Exception/NotFoundException.php',
  'wptools'=>'M/WPTools.php',
  'spyc'                  =>  'M/lib/spyc.php',
  'presenter'             => 'M/presenter.php'
  )
  );

function m_autoload_db_dataobject($class)
{
  $classname = strtolower($class);
  if(strpos($classname, 'dataobjects_') === 0) {
    return DB_DataObject::_autoloadClass($classname);
  } else {
    return false;
  }
}
//Mreg::append('autoloadcallback','autoload_db_dataobject');
/**
 *
 * Autoload
 *
 * @param	$class	string	Class to load
 * @return	boolean
 */
function M_autoload($class) {
  $classes = Mreg::get('autoload');
  // Switch to lowercase as PHP is not case sensitive for objects and methods while it is for array keys
  $class = strtolower($class);
	if(key_exists($class,$classes)) {
  require $classes[$class];
  return true;
}
if(preg_match('`^(.+)_hook$`i',$class,$match)) {
  @include 'lib/hooks/'.ucfirst($match[1]).'.php';
  if(class_exists($class)) {
    return true;
  }
  return false;
}
try {
  $callbacks = Mreg::get('autoloadcallback');
  if(is_array($callbacks)) {
  	foreach($callbacks as $callback) {
  		if(is_callable($callback)) {
        call_user_func($callback,$class);
      }
    }
  }
} catch (Exception $e) {

}
return false;
}


spl_autoload_register('M_autoload');
spl_autoload_register('m_autoload_db_dataobject');