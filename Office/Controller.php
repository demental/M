<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Controller mechanism. Mostly abstract, most of the M_Office modules extend from this class
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class M_Office_Controller {
  public function __construct($options = null) {
    $this->options = $options ? $options : PEAR::getStaticProperty(strtolower(get_class($this)), 'options');
  }
  public function hasOutput() {
    return !empty($this->localOutput['main']);
  }
  public function getOptions() {
	  return $this->options;
  }
  public static function &tplInstance($module=null)
  {
    $out = Mreg::get('tpl')->instance();
    if($module) {
      $out->setPaths(array(OFFICE_TEMPLATES_FOLDER,
                APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.'_shared'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR,
      APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR,
                            ));
    }
   return $out;
  }
  public function getGlobalOption($name,$module,$table = null, $merge = false) {
      $options = PEAR::getStaticProperty('m_office_'.$module,'options');
      return $this->grabOption($name,$table,$merge,$options);
  }
  public function getOption($name, $table = null, $merge = false) {
      return $this->grabOption($name,$table,$merge,$this->options);
  }
  public function grabOption($name, $table = null, $merge = false,$options) {    
      if ($merge == true) {
          $merged = array();
      }
      if ($table !== null) {
          if (isset($options['tableOptions'][$table][$name])) {
              if ($merge && is_array($options['tableOptions'][$table][$name])) {
                  $merged = $options['tableOptions'][$table][$name];
              } else {
                  return $options['tableOptions'][$table][$name];
              }
          }
          if (isset($this->mainOptions['tableOptions'][$table][$name])) {
              if ($merge) {
                  if (is_array($this->mainOptions['tableOptions'][$table][$name])) {
                      $merged = array_merge($this->mainOptions['tableOptions'][$table][$name], $merged);
                  } elseif ($merged) {
                      return $merged;
                  } else {
                      return $this->mainOptions['tableOptions'][$table][$name];
                  }
              } else {
                  return $this->mainOptions['tableOptions'][$table][$name];
              }
          }
      }
      if (isset($options[$name])) {
          if ($merge) {
              if (is_array($options[$name])) {
                  $merged = array_merge($options[$name], $merged);
              } elseif ($merged) {
                  return $merged;
              } else {
                  return $options[$name];
              }
          } else {
              return $options[$name];
          }
      }
      if (isset($this->mainOptions[$name])) {
          if ($merge) {
              if (is_array($this->mainOptions[$name])) {
                  $merged = array_merge($this->mainOptions[$name], $merged);
              } elseif ($merged) {
                  return $merged;
              } else {
                  return $this->mainOptions[$name];
              }
          } else {
              return $this->mainOptions[$name];
          }
      }
      if ($merge && $merged) {
          return $merged;
      }
      return true;
  }
	public function say ($message, $type = NULL) {
		@require_once 'M/Notifier.php';
		if(class_exists('Notifier')){
			$not=Notifier::getInstance();
			$not->broadCastMessage($this,$message , $type);
		}
	}
	public function assign($var,$val) {
	  Mreg::get('tpl')->assign($var,$val);
	}
	public function assignRef($var,&$val) {
	  Mreg::get('tpl')->assignRef($var,$val);
	}
	public function append($var,$val) {
	  Mreg::get('tpl')->append($var,$val);
	}
}