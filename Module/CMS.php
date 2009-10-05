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

class Module_CMS extends Module {

  protected $_dbtable='cms';
  protected $_dbstrip='strip';
  protected $_tpltitle='pageTitle';
  protected $_dbtitle='titrelong';
  protected $_dbmodulaction='modulaction';
  protected $_dbaccessibleaspage = 'accessibleaspage';
  protected $_dbisnode = 'isnode';
  protected $_forceaccessible = false;  
  protected $_redirToIndexIfNotFound = false;
  public function handleNotFound()
  {
    if($this->_redirToIndexIfNotFound) {
      $this->redirect301(strtolower(str_replace('Module_', '', get_class($this))).'/index');
    } else {
      $this->redirect404('error/404');
    }
  }
  protected function populateCMS()
  {
    $content = Mreg::get('content');
    if(!$this->_forceaccessible) {
      $content->{$this->_dbaccessibleaspage} = 1;
    }

    if(!$content->get($this->_dbstrip,$this->_dataAction)) {
      $this->handleNotFound();
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
  
  public function preExecuteAction($action)
  {
    $this->populateCMS();
  }
  public function executeAction($action)
  {
    $this->_dataAction = $this->_content->{$this->_dbmodulaction}?$this->_content->{$this->_dbmodulaction}:$action;
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
    try {
      $out = parent::output($template,$layout);
    } catch(Exception $e) {
      $out = parent::output(strtolower(str_replace('Module_', '', get_class($this))).'/index',$layout);
    }
    return $out;
  }
}