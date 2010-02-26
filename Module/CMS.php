<?php 

/**
* M PHP Framework
* @package      M
* @subpackage   Module_CMS
*/
/**
* M PHP Framework
*
* Module extension that automagically fetches and assigns to the view as database CMS record using the action value
*
* @package      M
* @subpackage   Module_CMS
* @author       Arnaud Sellenet <demental@sat2way.com>
* @copyright    Copyright (c) 2003-2009 Arnaud Sellenet
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

class CMS_Module extends Module {

  protected $_dbtable='cms';
  protected $_dbstrip='strip';
  protected $_tpltitle='pageTitle';
  protected $_dbtitle='titrelong';
  protected $_dbmodulaction='modulaction';
  protected $_dbaccessibleaspage = 'accessibleaspage';
  protected $_dbisnode = 'isnode';
  protected $_forceaccessible = false;  
  protected $_redirToIndexIfNotFound = false;
  public function handleNotFound($strip)
  {
    if($this->_redirToIndexIfNotFound) {
      $this->redirect301(strtolower(str_replace('Module_', '', get_class($this))).'/index');
    } else {
      $this->redirect404('error/404');
    }
  }
  protected function populateCMS($action)
  {
    try {
      $content = Mreg::get('content');
    } catch (Exception $e) {
      $content = DB_DataObject::factory($this->_dbtable);
    }
    if(!$this->_forceaccessible && !empty($this->_dbaccessibleaspage)) {
      $content->{$this->_dbaccessibleaspage} = 1;
    }
    if(key_exists('alias_id',$content->table())) {
      $content->whereAdd('alias_id is null');
    }
    if(!$content->get($this->_dbstrip,$action)) {
      $this->handleNotFound($action);
      return;
    }

    if($content->{$this->_dbisnode}) {
      $target = strtolower(str_replace('Module_', '', get_class($this))).'/'.$content->getPlugin('tree')->getFirstChild($content)->{$this->_dbstrip};
      $this->redirect301($target);
    }

    $this->assignRef('content',$content);
    $this->_content = $content;

    try{
      Mreg::set('content',$content);
    } catch (Exception $e) {
      
    }

    $this->assign($this->_tpltitle,$content->{$this->_dbtitle});

  }
  public function executeAction($action)
  {

    Mreg::get('setup')->setUpEnv();

    $this->populateCMS($action);

    $action = $this->_content->{$this->_dbmodulaction}?$this->_content->{$this->_dbmodulaction}:$action;

    try {

      parent::executeAction($action);

    } catch (Error404Exception $e) {
      if($this->_dbnotfound) {
        throw new Error404Exception('content data not found !');
      }
      $action='index';
      parent::executeAction($action);
    }

  }
  public function output($template=null,$layout=null)
  {
    if(is_null($template)) {
      $template = strtolower(str_replace('Module_', '', get_class($this))).'/'.$this->_content->{$this->_dbmodulaction};      
    }
    try {
      $out = parent::output($template,$layout);
    } catch(Exception $e) {
      $out = parent::output(strtolower(str_replace('Module_', '', get_class($this))).'/index',$layout);
    }
    return $out;
  }
}