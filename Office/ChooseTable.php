<?php


class M_Office_ChooseTable extends M_Office_Controller {
	function M_Office_ChooseTable() {
		M_Office_Controller::M_Office_Controller();
        $o = '<ul class="chooseTables">';
        $modules = $this->getOption('modulesToList');
        $officeConfig = PEAR::getStaticProperty('m_office','options');
        $moduleconf=$officeConfig['modules'];
        if ($modules) {
			    $diff = array_diff(array_keys($_GET),array('module'));
            foreach ($modules as $module) {
                    $o .= '<li';
                    if($_REQUEST['module']==$module || (is_array($module) && in_array($_REQUEST['module'],$module))){
                        $o.=' class="selected"';
                    }
                    if(is_array($module)) {
                        $modgroup = $module;
                        $module=array_shift($modgroup);
                    } else {
                        $modgroup=null;
                    }
                    $o.='>
										<a href="'.M_Office_Util::getQueryParams(array('module' => $module),
                                 $diff).'">'.$moduleconf[$module]['title'].'</a>';
                    if($modgroup) {
                        $o.='<ul>';
                        foreach($modgroup as $submod) {
                            $o.='<li><a href="'.M_Office_Util::getQueryParams(array('module' => $submod),$diff).'">'.$moduleconf[$submod]['title'].'</a></li>';
                        }
                        $o.='</ul>';

                    }
                    $o.='</li>';
                }
            $o .= '</ul>';
        } else {
            $o .= '</ul>No modules found';
        }
        $this->assign('choosetable',$o);
    }
}

?>