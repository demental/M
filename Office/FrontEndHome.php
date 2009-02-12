<?php
class M_Office_FrontEndHome extends M_Office_Controller {
    function M_Office_FrontEndHome() {
        M_Office_Controller::M_Office_Controller();
        $modinfo = &PEAR::getStaticProperty('Module','global');

        array_unshift($modinfo['template_dir'],APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR);
        array_push($modinfo['template_dir'],OFFICE_TEMPLATES_FOLDER);

        $subController = Module::factory('home',array(APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'modules/','M/Office/modules/'));
        $subController->executeAction($_REQUEST['action']?$_REQUEST['action']:'index');
        $this->assign('__action','dyn');
        if($subController->getConfig('layout',$_REQUEST['action']?$_REQUEST['action']:'index')=='__self') {
          M_Office::$dsp='__defaut/ajaxindex';
        }
        $this->assign('output',$subController->output(null,'__self'));
	  }    
}