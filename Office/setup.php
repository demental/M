<?php

class M_Office_Setup {
  public function setup()
  {
    M_Office_Util::$mainOptions = PEAR::getStaticProperty('m_office', 'options');

    M::addPaths('module', array(
      APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR, APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'modules/',
      'M/Office/modules/'));

    $modinfo = &PEAR::getStaticProperty('Module','global');
    array_unshift($modinfo['template_dir'],APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR);
    array_push($modinfo['template_dir'],OFFICE_TEMPLATES_FOLDER);

    // adding Theme is available
    if(Config::getPref('theme')) {
      array_push($modinfo['template_dir'],APP_ROOT.WEB_FOLDER.'/themes/'.Config::getPref('theme').'/templates/');
    }

    // TODO Check if requested module is valid

    $tplpaths = array(
      OFFICE_TEMPLATES_FOLDER,
      APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
      APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
      APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$_REQUEST['module'].DIRECTORY_SEPARATOR,
    );

    foreach(PluginRegistry::registeredPlugins() as $pluginName) {
      $tplpaths []= 'M/plugins'.DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
      $tplpaths []= 'M/plugins'.DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$_REQUEST['module'].DIRECTORY_SEPARATOR;
      $tplpaths []= APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;
      $tplpaths []= APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$pluginName.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$_REQUEST['module'].DIRECTORY_SEPARATOR;
    }

    if(Config::getPref('theme')) {
      $tplpaths[]= APP_ROOT.WEB_FOLDER.'/themes/'.Config::getPref('theme').'/templates/';
    }

    M::addPaths('template', $tplpaths);

    $tpl = new Mtpl($tplpaths);
    $tpl->assign('jsdir',SITE_URL.'js/');

    Mreg::set('tpl',$tpl);
  }
}
