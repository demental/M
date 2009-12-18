<?php
/**
 * M PHP Framework
 *
 * @package      M
 * @subpackage   Mtpl
 * @author       Arnaud Sellenet <demental@sat2way.com>
 * @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
 * @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
 * @version      0.1
 */

/**
 *
 * Bare bones php-based template engine
 *
 */
class Mtpl {
  
  protected $_addComments = true;
	protected $_assignvars = array();
	protected $_config;
	protected $_postFilters = array();
	protected $_module;
	protected static $_captures;
	private   $_currentCapture;
	private   $_currentFetch;
	protected static $_css=array();
	protected static $_js=array();
	protected static $_jsinline=array();
	protected static $_meta=array();

	function __construct($tpldir,$module=null)
	{
		if(!is_array($tpldir)) {
			$tpldir = array($tpldir);
		}
		$this->_config['tplfolders'] = $tpldir;
		$this->_module=$module;
		if('production'==MODE) {
		  $this->_addComments = false;
		}
	}
	/**
	 * Adds a string path to the current instance's paths array
	 * @param $path string new path (relative to PHP's include_paths)
	 * @param $pos string 'after' or 'before' : paths added before take precedence to other paths, after does not.
	 **/
	public function addPath($path,$pos='before')
	{
		switch($pos) {
			case 'before':
				array_unshift($this->_config['tplfolders'],$path);
				break;
			default:
				array_push($this->_config['tplfolders'],$path);
				break;
		}
	}
  /**
  * Set the instance's paths
  * @param $paths array of paths (relative to PHP's include_paths)
  **/
	public function setPaths($paths)
	{
		$this->_config['tplfolders'] = $paths;
	}	
	public function getPaths()
	{
	 return $this->_config['tplfolders'];
	}
	public function getCSS()
	{
		return Mtpl::$_css;
	}
	public function getJS()
	{
		return Mtpl::$_js;
	}
	public function getJSinline($event='ready')
	{
		return is_array(Mtpl::$_jsinline[$event])?Mtpl::$_jsinline[$event]:array();
	}
	public function addCSS($css,$media='screen',$conditional=null)
	{
		Mtpl::$_css[] = array('name'=>$css,'media'=>$media,'conditional'=>$conditional);
	}
	public function addJS($js)
	{

		if(is_array($js)) {
			Mtpl::$_js = array_merge(Mtpl::$_js,$js);
		} else {
			Mtpl::$_js[] = $js;
		}
		Mtpl::$_js = array_unique(Mtpl::$_js);
	}
	public function addJSinline($js,$event='ready')
	{
		Mtpl::$_jsinline[$event][] = $js;
	}
	public function setMeta($name,$content)
	{
		Mtpl::$_meta[$name]=$content;
	}
	public function getMeta($name)
	{
		return Mtpl::$_meta[$name];
	}
	public function &instance()
	{
		return new Mtpl($this->_config['tplfolders'],$module);
	}
	public function &getVars ()
	{
		return $this->_assignvars;
	}
	public function setVars (&$vars)
	{
		$this->_assignvars = &$vars;
	}
	public function assign ($var, $val)
	{

		$this->_assignvars[$var] = $val;
	}
	public function append($var,$val)
	{
		$this->_assignvars[$var][]=$val;
	}
	public function concat($var,$val)
	{
		$this->_assignvars[$var].=$val;
	}

	public function assignArray ($arr)
	{
		$this->_assignvars = array_merge($this->_assignvars, $arr);
	}
	public function assignRef ($var, &$val)
	{
		$this->_assignvars[$var] = &$val;
	}

	public function addPostFilter ( Mfilter $filter )
	{
		$this->_postFilters[] = &$filter;
	}


	public function display($tplfile, $cacheId = null)
	{
		echo $this->fetch($tplfile);
	}
	public function getTemplatePath()
	{
		$folders = array_reverse($this->_config['tplfolders']);
		foreach($folders as $folder) {
			if(FileUtils::file_exists_incpath($folder.$this->_tplfile.'.php')) {
				return $folder.$this->_tplfile.'.php';
			}
		}
		return false;
	}
	public function fetch($tplfile)
	{
		if(!is_array($tplfile)) {
			$tplfile = array($tplfile);
		}
		$buffer = ob_get_contents();
		ob_clean();
		extract($this->_assignvars);



		$included=false;
		foreach($tplfile as $file) {
			$this->_tplfile=$file;
			if($tpl = $this->getTemplatePath()) {
				ob_start();
        if(!empty($buffer)) {
          echo $this->comment('Start include '.$file);
        }
        include($tpl);
        if(!empty($buffer)) {
          echo $this->comment('End include '.$file);
        }
				$included=true;
				$ret = ob_get_contents();
				/*      foreach($this->_postFilters as $filter) {
				 $filter->execute($ret);
				 }*/

				ob_clean();
				echo $buffer;
				return $ret;
				break;
			}
		}
		echo $buffer;
		ob_clean();
		throw new Exception('Fichier '.print_r($tplfile,true).' introuvable dans dossier(s) '.print_r($this->_config['tplfolders'],true));
	}
	/**
	 * Template helpers (for use in the template as $this->methodName)
	 **/
	private function startCapture($capturename) {
		$this->_currentFetch=ob_get_contents();
		ob_clean();
		$this->_currentCapture=$capturename;
		ob_start();
	}
	private function endCapture() {
		$res = ob_get_contents();
		ob_clean();
		ob_start();
		echo $this->_currentFetch;
		Mtpl::$_captures[$this->_currentCapture]=$res;
	}
	public function getCapture($name) {
		return Mtpl::$_captures[$name];
	}
	// partial inclusion
	private function includetpl($file, $params = null,$autoglobal = false)
	{
		return $this->i($file, $params = null,$autoglobal = false);
	}
	private function i($file, $params = null,$autoglobal = false)
	{
		$tpl = & new Mtpl($this->_config['tplfolders']);
		if($autoglobal) {
			$tpl->setVars($this->getVars());
		}
		if(is_array($params)) {
			foreach($params as $var=>&$value) {
				if(is_object($value)) {
					$tpl->assignRef($var, $value);
				} else {
					$tpl->assign($var, $value);
				}
			}
		}
		$tpl->display($file);
	}
	// Render HTML_QuickForm
	private function rf(&$form,$type='dynamic') {
		if(is_array($form)) {
			return $form;
		}
		if($type=='dynamic') {
			require_once 'M/HTML/QuickForm/Renderer/Array.php';
			$r = new HTML_QuickForm_Renderer_Array(true,true);
			if(!is_object($form)) {
				throw new Exception('Object is not a form object');
			}
			$form->accept($r);
			$ret = $r->toArray();
			if(!count($ret['sections'])) {
				$ret['sections'] = array(array('elements'=>$ret['elements']));
			}
		} else {
			require_once 'M/HTML/QuickForm/Renderer/ArrayStatic.php';
			$r = new HTML_QuickForm_Renderer_ArrayStatic(true,true);
			$form->accept($r);
			$ret = $r->toArray();

		}
		return $ret;
	}
	// Echo
	private function e($var) {
		echo $var;
	}

	/**
	 * Adds a component to the template
	 * @param string module name
	 * @param string module action
	 * @return string rendered module
	 **/
	private function component($componentId,$action='index',$params=null) {
		return $this->c($componentId,$action,$params);
	}

	private function c($componentId,$action='index',$params=null) {

		if(is_object($this->_module)) {
			$conf = $this->_module->getConfig('component_'.$componentId,$this->_module->getCurrentAction());
			if(!is_array($conf)) {
				$conf = $this->_module->getConfig('component_'.$componentId);
			}
		} else {
			$conf=null;
		}
		if(is_array($conf)) {
			$module = $conf[0];
			$action = empty($conf[1])?'index':$conf[1];
		} elseif($conf=='__none') {
		  return;
	  } else {
			$module = $componentId;
		}
		$c = new Component($module, $action,$params);

		$c->execute();

    return $this->comment('Start component '.$module.'/'.$action.' routed to '.$c->getPage()->getCurrentModule().'/'.$c->getPage()->getCurrentAction())
      .$c->display()
      .$this->comment('End component '.$module.'/'.$action);
	}
	
	
	
	public function toArray() {
		return $this->_assignvars;
	}
	public function comment($comment)
	{
	  if(!$this->_addComments) return '';
	  return '
<!-- '.$comment.' -->
';
	}
	public function img($filename,$subfolders=null,$mainfolder = null)
	{
    if(is_null($mainfolder)) {
      $mainfolder='images/';
    }
    if(!is_array($subfolders)) {
      return SITE_URL.$mainfolder.$filename;
    } else {
      foreach($subfolders as $afolder) {
        if(file_exists(APP_ROOT.WEB_FOLDER.'/'.$mainfolder.$afolder.'/'.$filename)) {
          return SITE_URL.$mainfolder.$afolder.'/'.$filename;
        }
      }
    }
	}
	public function localeimg($filename,$mainfolder = 'images/locale/')
	{
    return $this->img($filename,array(T::getLang(),substr(T::getLang(),0,2)),$mainfolder);
	}
	
	public function printJS()
	{
	  $out='';
    foreach (Mtpl::getJS() as $js) {
      $out.='
<script type="text/javascript" src="/js/'.$js.'.js"></script>';
    }
    echo $out;
	}
	public function printCSS()
	{
    $out='';
    foreach (Mtpl::getCSS() as $css) {
      if (is_null($css['conditional'])) {
      $out.='
<link rel="stylesheet" type="text/css" href="/css/'.$css['name'].'.css" media="'.$css['media'].'" />';
  } else {
      $out.='
<!--[if lt '.$css['conditional'].']>
    <link rel="stylesheet" type="text/css" href="/css/'.$css['name'].'.css" media="'.$css['media'].'" />
<![endif]-->';
  }
    }
    echo $out;
	}
	public function printJSinline($event='ready')
	{
    $out='';
    foreach(Mtpl::getJSinline($event) as $line) {
      $out.=$line."\n";
    }
    $out = trim($out);
    if(!empty($out)) {
      echo '
<script type="text/javascript">
$(function() {
'.$out.'
});
</script>';
    }
	}
	
}