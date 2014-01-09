<?php

$paths[]=APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR;
$paths[]=APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR;
$paths[]=APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR;
set_include_path(get_include_path().':'.implode(':',$paths));

require 'PEAR.php';
require 'M/M_autoload.php';

if(defined('E_DEPRECATED')) {
  ini_set('error_reporting',E_ALL & ~E_NOTICE & ~E_DEPRECATED);
} else {
  ini_set('error_reporting',E_ALL & ~E_NOTICE);
}
switch(MODE) {
  case 'development' :
    ini_set('display_errors',1);
    $caching=false;
    break;
  case 'test' :
    ini_set('display_errors',1);
    $caching=false;
    break;
  case 'production' :
    ini_set('display_errors',0);
    $caching=true;
    break;
}

T::setConfig(array(
  'path' => APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'lang/',
  'encoding' => 'utf8',
  'saveresult' => false,
  'driver' => 'reader',
  'autoexpire' => (MODE == 'development')
  )
);

if(!defined('DEFAULT_LANG')) define('DEFAULT_LANG', 'en');
$lang = $_REQUEST['lang'] ? $_REQUEST['lang'] : DEFAULT_LANG;
T::addPath(dirname(__FILE__).'/lang/');
T::setLang($lang);

$opt = &PEAR::getStaticProperty('Module', 'global');
$opt['template_dir'] = array(APP_ROOT.DIRECTORY_SEPARATOR.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,APP_ROOT.DIRECTORY_SEPARATOR.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR);
$opt['caching'] = $caching;
$opt['cacheDir'] = APP_ROOT.DIRECTORY_SEPARATOR.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
$opt['cacheTime'] = 7200;
$dispatchopt = &PEAR::getStaticProperty('Dispatcher', 'global');
$dispatchopt['all']['loginmodule']='user';
$dispatchopt['all']['loginaction']='login';
$dispatchopt['all']['modulepath']=array('modules');

include APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'config.php';
include APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'config.php';
include APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'setup.php';
$setup = new M_setup();

Mreg::set('setup',$setup);