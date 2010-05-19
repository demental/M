<?php
/**
* M PHP Framework
* @package      M
* @subpackage   M_Office
*/
/**
* M PHP Framework
*
* Utilities library for M_Office App
*
* @package      M
* @subpackage   M_Office
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

/**
* Helper methods library (mostly static) used by office application.
**/
class M_Office_Util {
  public static $mainOptions;
  public static $fieldCache = array();
  /**
   * Redirects to another url (uses javascript is some headers were already sent)
   * @param url string url to redirect to
   */
  public static function refresh($url = false) {
          if (empty($url) || substr($url, 0, 4) !== 'http') {
              $url = 'http'.(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 's' : '').'://'.$_SERVER['HTTP_HOST'].
                  (!empty($url) && $url[0] == '/'
                   ? ''                                      //if the URL is already from the root
                   : (empty($url) || $url[0] == '?'
                      ? $_SERVER['PHP_SELF']                 //if the url is empty or starts with ? prefix with current dir & script
                      : dirname($_SERVER['PHP_SELF']).'/')). //if the url is not empty and does not start with ? add current dir
                  $url;
          }
          if ((stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE') && stristr($_SERVER['HTTP_USER_AGENT'], 'Mac')) || headers_sent()) {
                echo '<script language="JavaScript1.1">
  <!--
  location.replace("'.$url.'");
  //-->
  </script>
  <noscript>
  <meta http-equiv="Refresh" content="0; URL='.$url.'"/>
  </noscript>
  Please <a href="'.$url.'">click here</a> to continue.
  ';
          } else {
              header('Location: '.$url);
          }
          exit;
      }
  /**
   * Redirects using a POST form to send back post variables
   * @param url target URL
   * 
   */
  public static function postRedirect($url,$params,$template = null,$templateparams = null)
  {
    $form = new MyQuickForm('redirectform','POST',$url);
    foreach($params as $k=>$v) {
      self::addHiddenField($form,$k,$v);
    }
    if(is_null($template)) {
      $template = 'postredirect';
    }
    if(is_null($templateparams)) {
      $templateparams = array();
    }
    foreach($templateparams as $k=>$v) {
      Mreg::get('tpl')->assign($k,$v);
    }
    Mreg::get('tpl')->assign('redirectform',$form);
    Mreg::get('tpl')->assign('__action',$template);
    echo Mreg::get('tpl')->fetch(M_Office::$dsp);
    die;
  }

  /**
   * Returns the name displayed in the interface for a module
   * @param  $module  string module name
   * @return string   name of the module
   */
  public static function getFrontTableName($module) {
      $op = PEAR::getStaticProperty('m_office', 'options');
      $modlist = $op['modules'];
      if(key_exists($module,$modlist)) {
          return $modlist[$module]['title'];
      }
      return $module;
  }
  public static function getAjaxQueryParams($params = array(), $remove = array(), $entities = false) {
      return self::getQueryParams(array_merge($params,array('ajax'=>1)),$remove,$entities);
  }
  /**
   * Builds and URI, starting from the current GET request,
   * merging passed params as first parameter and excluding variable names passed as second (optional) parameter
   * @param $params     array  associative array to be passed as the GET request (multidimensional arrays are handled too)
   * @param $remove     array  indexed array of variable names to be excluded from the uri
   * @param $entities   bool   should the query be HTML-escaped ?
   */
  public static function getQueryParams($params = array(), $remove = array(), $entities = false) {
      $ret = '';
      $arr = array();
      if (!isset($params['regenerate'])) {
          $remove[] = 'regenerate';
      }
      $get=$_GET;
      $get=array_diff_key($get,array_flip($remove));

      foreach (array_merge($get, $params) as $key => $val) {
//            if (substr($key, 0, 5) != '_qf__' && $key!=) {
              $arr[$key] = $val;
//            }
      }
      $ret = self::queryString($arr);
      if ($entities) {
          $ret = htmlentities($ret, ENT_QUOTES);
      }
      $ret = $ret ? '?'.$ret : '';
      return ROOT_ADMIN_URL.ROOT_ADMIN_SCRIPT.$ret;

  }
  /**
   * recursively builds a query string from passed parameters
   * @param  $params  array  associative array of query params
   * @param  $prefix  string variable names prefix 
   * @param  $postfix string variable names postfix
   * @return string built query
   */
  public static function queryString($params, $prefix = '', $postfix = '') {
      $ret = '';
      foreach ($params as $key => $val) {
          if ($ret) {
              $ret .= '&';
          }
          if (is_array($val)) {
              $ret .= self::queryString($val, $prefix.$key.$postfix.'[', ']');
          } else {
              $ret .= urlencode($prefix.$key.$postfix).'='.urlencode((string)$val);
          }
      }
      return $ret;
  }
  /**
   * returns HTML hidden fields (even arrays) of current variables declared in POST (and optionally GET)
   * @param $remove array indexed array of var names to exclude from the fields
   * @param $get bool should vars declared in GET be rendered too ?
   * @return string rendered HTML of hidden fields
   */
  public static function hiddenFields($remove = array(), $get = false)
  {
    $out='';
    foreach ($_POST as $key => $val) {
        if (!in_array($key, $remove)) {
            $out.=self::hiddenField($key, $val);
        }
    }
    if ($get) {
        foreach ($_GET as $key => $val) {
            if (!in_array($key, $remove)) {
                $out.=self::hiddenField($key, $val);
            }
        }
    }
    return $out;    
  }
  /**
   * returns HTML hidden fields (even arrays) of one name/value pair
   * @param $key string key name
   * @param $val mixed  value (string or associative array)
   **/
  public static function hiddenField($key,$val)
  {
    $out='';
    if (is_array($val)) {
        foreach ($val as $name => $aval) {
            $out.=self::hiddenField($key.'['.$name.']', $aval);
        }
    } else {
      $h = HTML_QuickForm::createElement('hidden',$key,$val);
      return $h->toHtml();
    }
    return $out;
  }

  public static function addHiddenFields(&$form, $remove = array(), $get = false) {
      foreach ($_POST as $key => $val) {
          if (!in_array($key, $remove) && !$form->elementExists($key)) {
              self::addHiddenField($form, $key, $val);
          }
      }
      if ($get) {
          foreach ($_GET as $key => $val) {
              if (!in_array($key, $remove) && !$form->elementExists($key)) {
                  self::addHiddenField($form, $key, $val);
              }
          }
      }
  }

  public static function addHiddenField(&$form, $name, &$value) {
      if (is_array($value)) {
          foreach ($value as $key => $val) {
              self::addHiddenField($form, $name.'['.$key.']', $val);
          }
      } else {
          $form->addElement('hidden', $name, $value);
      }
  }

 	public static function &getSearchForm(&$do){

    if(CACHE_FORMS===true) {
      $cacheName = 'searchform_'.$do->tableName();
      $options = array(
          'caching' =>true,
          'cacheDir' => APP_ROOT.PROJECT_NAME.DIRECTORY_SEPARATOR.APP_NAME.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'forms/',
          'lifeTime' => 3600,
          'fileNameProtection'=>false,
  		);

  		$cache = new Cache_Lite($options);
  		if($_cachedData = $cache->get($cacheName)) {
        Mreg::append('autoloadcallback',array(array('MyQuickForm','autoloadElements')));
  		  $cachedform = unserialize($_cachedData);
        $cachedform->updateAttributes(array('action'=>self::getQueryParams(array(), array('page'), false)));
        return $cachedform;
  		}

  	}	
    $do->fb_selectAddEmpty = array();
  	if(is_array($do->links())){
      foreach ($do->links() as $field => $link) {
        $do->fb_selectAddEmpty[] = $field;
      }
  	}
		if(is_array($do->fb_enumFields)){
			foreach ($do->fb_enumFields as $field){
	       $do->fb_selectAddEmpty[] = $field;
			}
		}
    $do->fb_formHeaderText=__('Search');
    $do->fb_submitText='>>';
    $do->fb_linkNewValue = false;

    $formBuilder =& MyFB::create($do);
    $formBuilder->preGenerateFormCallback=array($do,'prepareSearchForm');
    $do->prepareSearchForm($fb);
    $do->fb_userEditableFields=$do->fb_fieldsToRender;

    $formBuilder->postGenerateFormCallback=array($do,'postPrepareSearchForm');
    $form = new MyQuickForm(  'formSearch',
                              'GET',
                              self::getQueryParams(array(), array('page'), false));
   	$formBuilder->useForm($form);

      
    $formBuilder->getForm();
    $form->addElement('hidden', 'searchSubmit', 1);
    $form->_rules = array();
    $form->_formRules = array();
    $form->_required = array();
    self::addHiddenFields($form, array('search', 'page','__dontpaginate'), true);
    $form->addElement('checkbox','__dontpaginate','Afficher les résultats sur une seule page');
    if($cache) {
      $cache->save(serialize($form));
    }

	  return $form;
	}
	public static function outputform(&$form,$template='bordered',$addwait = true) {
    if($addwait) {
        $formId=$form->getAttribute('id');
        Mtpl::addJSinline('
        $("#'.$formId.' input:submit").click(function() {
            $(\'<span><img src="images/indicator.gif" />'.__('Loading... Please wait').'</span>\').prependTo($(this).parent());
        });
        ','ready');
    }
		$options = PEAR::getStaticProperty('M_Office', 'options');
		$tpl = Mreg::get('tpl')->instance();
		$tpl->assignRef('form',$form);
		return $tpl->fetch('form-'.$template);
	}
  public static function &doFortable($table) {      
    $do = DB_DataObject::factory($table);
  	$viewOptions = PEAR::getStaticProperty('m_office_showtable', 'options');
  	if($viewOptions['tableOptions'][$table]['filters']){
      foreach($viewOptions['tableOptions'][$table]['filters'] as $filter) {
        if(is_array($filter)) {
          foreach($filter as $k=>$e) {
            $do->{$k} = $e;
          }
        } else {
    	    $do->whereAdd(preg_replace('`U::([a-zA-Z0-9_]+)`e',"User::getInstance('office')->getDBDO()->$1",$filter));
        }
      }
  	}
    return $do;
  }
  public static function getModuleInfo($module)
  {
  	$op = PEAR::getStaticProperty('m_office', 'options');
    $mod = $op['modules'][$module];
    return $mod;
  }
  public static function &doForModule($module,$filters=true) {      
      $mod = self::getModuleInfo($module);
      $do = DB_DataObject::factory($mod['table']);

    	$AuthOptions = PEAR::getStaticProperty('m_office_auth', 'options');
    	if($AuthOptions['ownership']){
    		$do->filterowner=User::getInstance('office')->getProperty('level')!=NORMALUSER?false:User::getInstance('office')->getProperty('groupId');
        if($p = &$do->getPlugin('ownership')) {
    			$p->userIsInAdminMode(User::getInstance('office')->getProperty('level'));
    		}
    	}
      $filterArray = $mod['filters'];
      if(!is_array($filterArray)) {
        $filterArray = array();
      }
      $shT = PEAR::getStaticProperty('m_office_showtable','options');
      if(!is_array($shT['tableOptions'][$module]['filters'])) {
        $shT['tableOptions'][$module]['filters']=array();
      }
      $filterArray=array_merge($filterArray,$shT['tableOptions'][$module]['filters']);
    	if($filterArray && $filters){
        foreach($filterArray as $filter) {
          if(is_array($filter)) {
            foreach($filter as $k=>$e) {
              $do->{$k} = $e;
            }
          } else {
            $db = $do->getDatabaseConnection();
      	    $do->whereAdd(preg_replace('`U::([a-zA-Z0-9_]+)`e',"\$db->quote(User::getInstance('office')->getDBDO()->$1)",$filter));
          }
        }
    	}
      return $do;
  }
	public static function isCached($id) {
	    return false;
	}
	public static function buildCache($data, $id) {
	    return;
	}
	public static function retrieveFromCache($id) {
	    return;
	}
	public static function clearRequest($values) {
        if(!is_array($values)) {
            $values = array();
        }
		$values['doaction']=1;
		$values['glaction']=1;
		$values['doSingleAction']=1;
		$values['_qf__actionparamsForm']=1;

		    foreach($values as $k=>$v) {
		        unset($_REQUEST[$k]);
		        unset($_POST[$k]);		    
		        unset($_GET[$k]);		    
            }
		unset($values['glaction']);
		unset($values['doSingleAction']);
	}
	public static function field_format_bypass($obj,$field) 
	{
	 return $obj->$field;
	}
	public static function field_format_bool($obj,$field) 
	{
	 return $obj->$field?'<span class="yes">'.__('Oui').'</span>':'<span class="no">'.__('Non').'</span>';
	}
	public static function field_format_enum($obj,$field) {
    return $obj->fb_enumOptions[$field][$obj->$field];
  }
	public static function field_format_link($obj,$field) {
    $identifier = $obj->tableName().'_field_'.$obj->$field;
    if(key_exists($identifier,self::$fieldCache)) return self::$fieldCache[$identifier];
    if(is_object($obj->{'_'.$field})) {
      return $obj->{'_'.$field}->__toString();
    }
	  $link = $obj->getLink($field);
	  if(is_object($link)) {
      foreach(array(array($link,'toHtmlCell'),array($link,'toHtml'),array($link,'__toString'),array('DB_DataObject_FormBuilder','getDataObjectString')) as $m) {
          if(method_exists($m[0],$m[1])){
              $res =  call_user_func($m,$link);
              break;
          }
      }
    } else {
      $res =  'n/a';
    }
    self::$fieldCache[$identifier] = $res;
    return $res;
	}
  public static function getGlobalOption($name,$module,$table = null, $merge = false) {
      $options = PEAR::getStaticProperty('m_office_'.$module,'options');
      return self::grabOption($name,$table,$merge,$options);
  }
  public static function grabOption($name, $table = null, $merge = false,$options) {    
    
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
          if (isset(self::$mainOptions['tableOptions'][$table][$name])) {
              if ($merge) {
                  if (is_array(self::$mainOptions['tableOptions'][$table][$name])) {
                      $merged = array_merge(self::$mainOptions['tableOptions'][$table][$name], $merged);
                  } elseif ($merged) {
                      return $merged;
                  } else {
                      return self::$mainOptions['tableOptions'][$table][$name];
                  }
              } else {
                  return self::$mainOptions['tableOptions'][$table][$name];
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
      if (isset(self::$mainOptions[$name])) {
          if ($merge) {
              if (is_array(self::$mainOptions[$name])) {
                  $merged = array_merge(self::$mainOptions[$name], $merged);
              } elseif ($merged) {
                  return $merged;
              } else {
                  return self::$mainOptions[$name];
              }
          } else {
              return self::$mainOptions[$name];
          }
      }
      if ($merge && $merged) {
          return $merged;
      }
      return true;
  }
  /**
   * applies the method quote to an item.
   * This method must be used with array_walk()
   * @param string by reference
   * @param string
   * @param $dbcnx an instance of MDB2
   */
  public static function arrayquote(&$item,$key,$dbcnx)
  {
    $item = $dbcnx->quote($item);
  }
}