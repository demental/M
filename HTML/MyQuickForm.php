<?php
/**
* M PHP Framework
* @package      M
* @subpackage   MyQuickForm
*/
/**
* M PHP Framework
*
* HTML_QuickForm extension
* Main goal is to be able to init the form with an arbitrary request array for unit testing purposes.
*
* @package      M
* @subpackage   MyQuickForm
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

require_once 'HTML/QuickForm.php';
class MyQuickForm extends HTML_QuickForm {

  function MyQuickForm($formName='', $method='post', $action='', $target='', $attributes=null, $trackSubmit = false)
  {
      HTML_Common::HTML_Common($attributes);
      $method = (strtoupper($method) == 'GET') ? 'get' : 'post';
      $action = ($action == '') ? $_SERVER['PHP_SELF'] : $action;
      $target = empty($target) ? array() : array('target' => $target);
      $attributes = array('action'=>$action, 'method'=>$method, 'name'=>$formName, 'id'=>$formName) + $target;
      $this->_trackSubmit = $trackSubmit;
      $this->updateAttributes($attributes);
      $this->initRequest();

      if (preg_match('/^([0-9]+)([a-zA-Z]*)$/', ini_get('upload_max_filesize'), $matches)) {
          // see http://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
          switch (strtoupper($matches['2'])) {
              case 'G':
                  $this->_maxFileSize = $matches['1'] * 1073741824;
                  break;
              case 'M':
                  $this->_maxFileSize = $matches['1'] * 1048576;
                  break;
              case 'K':
                  $this->_maxFileSize = $matches['1'] * 1024;
                  break;
              default:
                  $this->_maxFileSize = $matches['1'];
          }
      }    
  } // end constructor
  public function hasRequestParam($param)
  {
    return isset($this->__request[$param]);
  }
  public function initRequest($get=null,$post=null,$request=null,$files=null)
  {
    $this->__get = is_array($get)?$get:$_GET;
    $this->__post = is_array($post)?$post:$_POST;    
    $this->__request = is_array($request)?$request:$_REQUEST;
    $this->__files = is_array($files)?$files:$_FILES;
    if (!$this->_trackSubmit || $this->hasRequestParam('_qf__' . $this->getAttribute('name'))) {

        if (1 == get_magic_quotes_gpc()) {
            $this->_submitValues = $this->_recursiveFilter('stripslashes', 'get' == $this->getAttribute('method')? $this->get_Get(): $this->get_Post());
            foreach ($this->get_Files() as $keyFirst => $valFirst) {
                foreach ($valFirst as $keySecond => $valSecond) {
                    if ('name' == $keySecond) {
                        $this->_submitFiles[$keyFirst][$keySecond] = $this->_recursiveFilter('stripslashes', $valSecond);
                    } else {
                        $this->_submitFiles[$keyFirst][$keySecond] = $valSecond;
                    }
                }
            }
        } else {
            $this->_submitValues = 'get' == $this->getAttribute('method')? $this->get_Get(): $this->get_Post();
            $this->_submitFiles  = $this->get_Files();
        }
        $this->_flagSubmitted = count($this->_submitValues) > 0 || count($this->_submitFiles) > 0;
    }
    if ($this->_trackSubmit) {
        unset($this->_submitValues['_qf__' . $this->getAttribute('name')]);
        $this->addElement('hidden', '_qf__' . $this->getAttribute('name'), null);
    }

  }
	public function &get_Get()
	{
	 return $this->__get;
	}
	public function &get_Post()
	{
	 return $this->__post;
	}
	public function &get_Files()
	{
	 return $this->__files;
	}
	public function getRequestValue($field)
	{
    return $this->__request[$field];
	}
	function toFrozenHtml ($in_data = null)
  {
		$this->freeze();
    if (!is_null($in_data)) {
        $this->addElement('html', $in_data);
    }
		require_once 'classes/FrozenRenderer.php';
    $renderer =& new HTML_QuickForm_FrozenRenderer();
    $this->accept($renderer);
    return $renderer->toHtml();
	} // end func toFrozenHtml
    
	public static function autoloadElements($className)
	{
	 if(preg_match('`HTML_QuickForm_(.+)`i',$className,$matches)) {
     $match= $matches[1];
	   switch($match) {
	     case 'advandate':
	      require_once 'M/HTML/QuickForm/advandate.php';
	      return;
	     default:
	       require_once 'HTML/QuickForm/'.$match.'.php';
	       return;
	   }
	 }
	 return false;
	}
	
}
