<?php
/**
* M PHP Framework
* @package      M
*/
/**
* M PHP Framework
*
* boot file. Must be called in every controller (web or CLI)
*
* @package      M
* @author       Arnaud Sellenet <demental at github>

* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


$paths = array();
/**
 * app root detection (this file MUST be in the app root)
 **/
define ('APP_ROOT',realpath(dirname(__FILE__)).'/');

/**
 * Include host specific config file
 **/
 if(!$host) {
$host = $_SERVER['HTTP_HOST'];
}
include APP_ROOT.'/config.'.$host.'.php';
if(!defined('TMP_PATH')) {
  define('TMP_PATH',APP_ROOT.'temp/');
}


/**
 * add include_paths if provided in host config file
 **/
$paths[]=APP_ROOT.'app/'.APP_NAME.'/';
if(is_array($paths)) {

  ini_set('include_path', ini_get('include_path').':'.implode(':',$paths));
}

/**
 * runtime mode
 **/
switch(MODE) {
	case 'development' :
		ini_set('error_reporting',E_ALL ^ E_NOTICE);
		ini_set('display_errors',1);
    $caching=false;
		break;
	case 'test' :
		ini_set('error_reporting',E_ALL ^ E_NOTICE);
		ini_set('display_errors',1);
    $caching=false;
		break;
	case 'production' :
		ini_set('error_reporting',E_ALL ^ E_NOTICE);
		ini_set('display_errors',0);
    $caching=true;
		break;
}
/**
 * Translation initialization (TODO move this out of the startup file, maybe setup)
 */
T::setConfig(array('path'=>APP_ROOT.'app/'.APP_NAME.'/lang/','encoding'=>'utf-8','saveresult'=>false,'cacheDir'=>APP_ROOT.'app/'.APP_NAME.'/cache/'));
// TODO define a default lang, not french hardcoded



$opt = &PEAR::getStaticProperty('Module', 'global');
$opt['template_dir'] = array(APP_ROOT.'app/'.APP_NAME.'/templates/');
$opt['caching'] = $caching;
$opt['cacheDir'] = APP_ROOT.'app/'.APP_NAME.'/cache/'.$host.'/';
$opt['cacheTime'] = 7200;
$dispatchopt = &PEAR::getStaticProperty('Dispatcher', 'global');
$dispatchopt['all']['loginmodule']='user';
$dispatchopt['all']['loginaction']='login';
$dispatchopt['all']['modulepath']=array('modules');

/**
* TODO config & setup : maybe we should rename them to pre-cache & post-cache config ?
**/
include APP_ROOT.'app/config.php';
include APP_ROOT.'app/'.APP_NAME.'/config.php';
include APP_ROOT.'app/setup.php';

$setup = new M_setup();
Mreg::set('setup',$setup);
