<?php 
// ============================================================================================================
// = Module extension that automagically fetches and assigns to the view as CMS record using the action value =
// ============================================================================================================
class Module_CMS extends Module {

  protected $_dbtable='cms';
  protected $_dbstrip='strip';
  protected $_tpltitle='pageTitle';
  protected $_dbtitle='titrelong';

  protected function populateCMS()
  {
    $content = DB_DataObject::factory($this->_dbtable);
    if(!$content->get($this->_dbstrip,$this->_dataAction)) {
      $this->_dbnotfound=1;
      return;
    }
    $this->assignRef('content',$content);
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
    $this->_dataAction = $action;
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
}?>