<?php

class M_Office_Setup {
  public function setup()
  {
    M_Office_Util::$mainOptions = PEAR::getStaticProperty('m_office', 'options');

    M::addPaths('module', array(
      APP_ROOT.'app/_shared/modules/',
      APP_ROOT.'app/'.APP_NAME.'/modules/',
      'M/Office/modules/'));

    M::addPaths('template', array(
      OFFICE_TEMPLATES_FOLDER,
      APP_ROOT.'app/_shared/templates/',
      APP_ROOT.'app/'.APP_NAME.'/templates/'
    ));

    if(Config::getPref('theme')) {
      M::addPaths('template', array(APP_ROOT.WEB_FOLDER.'/themes/'.Config::getPref('theme').'/templates/'));
    }

    $tpl = new Mtpl(M::getPaths('template'));
    $tpl->assign('jsdir',SITE_URL.'js/');

    Mreg::set('tpl',$tpl);
  }
}
