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
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Choosetable module creates a list of available modules for current user
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/


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